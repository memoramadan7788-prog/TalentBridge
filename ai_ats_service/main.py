import os
import uvicorn
from fastapi import FastAPI, UploadFile, File, Form, HTTPException
from fastapi.responses import HTMLResponse, JSONResponse
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
from typing import Optional, List, Dict, Any

from ats_engine import evaluate_cv
from resume_parser import parse_resume, analyze_words

app = FastAPI(
    title="AI ATS Recruitment Engine",
    description="Local AI-powered Applicant Tracking System using Ollama + Advanced PDF/Word Parsers",
    version="1.2.0"
)

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

class EvaluateRequest(BaseModel):
    resume_text: str
    job_description: str
    job_title: Optional[str] = None
    model_name: Optional[str] = "llama3.2"

@app.get("/", response_class=HTMLResponse)
async def serve_ui():
    html_path = os.path.join(os.path.dirname(__file__), "index.html")
    with open(html_path, "r", encoding="utf-8") as f:
        return f.read()

@app.get("/api/health")
async def health_check():
    return {
        "status": "ok",
        "service": "AI ATS Engine",
        "ollama_model": "llama3.2",
        "parsers": ["pdfplumber", "PyMuPDF (fitz)", "python-docx"]
    }

@app.post("/api/ats/evaluate-text")
def evaluate_text(req: EvaluateRequest):
    try:
        cv_stats = analyze_words(req.resume_text)
        jd_stats = analyze_words(req.job_description)
        
        result = evaluate_cv(
            resume_text=req.resume_text,
            job_description=req.job_description,
            job_title=req.job_title,
            model_name=req.model_name or "llama3.2"
        )
        result["cv_word_count"] = cv_stats["total_words"]
        result["jd_word_count"] = jd_stats["total_words"]
        return result
    except HTTPException:
        raise
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

@app.post("/api/ats/evaluate-file")
def evaluate_file(
    file: UploadFile = File(...),
    job_description: str = Form(...),
    job_title: Optional[str] = Form(None),
    model_name: Optional[str] = Form("llama3.2")
):
    try:
        content = file.file.read()
        parsed = parse_resume(content, file.filename)
        resume_text = parsed["text"]
        
        if not resume_text or len(resume_text.strip()) < 20:
            if parsed.get("is_scanned"):
                raise HTTPException(
                    status_code=400,
                    detail="This file appears to be a scanned image or photo without selectable text. Please upload a text-based PDF or Word document, or use the 'Paste Text' tab."
                )
            raise HTTPException(
                status_code=400,
                detail=f"Could not extract readable text from '{file.filename}'. Please make sure the file contains selectable text, or copy and paste the resume text in the 'Paste Text' tab."
            )
            
        jd_stats = analyze_words(job_description)
        
        result = evaluate_cv(
            resume_text=resume_text,
            job_description=job_description,
            job_title=job_title,
            bold_keywords=parsed.get("bold_keywords", []),
            model_name=model_name or "llama3.2"
        )
        result["filename"] = file.filename
        result["cv_word_count"] = parsed["word_count"]
        result["cv_unique_words"] = parsed["unique_words_count"]
        result["jd_word_count"] = jd_stats["total_words"]
        result["extractor_engine"] = parsed["engine"]
        result["bold_keywords"] = parsed.get("bold_keywords", [])
        result["extracted_full_text"] = resume_text
        result["extracted_text_preview"] = resume_text[:400] + ("..." if len(resume_text) > 400 else "")
        return result
    except HTTPException:
        raise
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

if __name__ == "__main__":
    uvicorn.run(app, host="127.0.0.1", port=8500)
