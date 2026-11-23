# TalentBridge — Local AI ATS Recruitment Engine

[![FastAPI](https://img.shields.io/badge/FastAPI-0.100%2B-009688.svg?logo=fastapi&logoColor=white)](https://fastapi.tiangolo.com)
[![Python](https://img.shields.io/badge/Python-3.10%2B-blue.svg?logo=python&logoColor=white)](https://python.org)
[![Ollama](https://img.shields.io/badge/Ollama-Llama_3.2-black.svg?logo=ollama&logoColor=white)](https://ollama.ai)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

An offline, privacy-first **AI-Powered Applicant Tracking System (ATS) Engine** designed for **TalentBridge**. It performs intelligent semantic CV parsing (PDF / DOCX) and matches candidate competencies against job descriptions using local Large Language Models (**Ollama Llama 3.2**) with zero external cloud dependencies.

---

## 🌐 The TalentBridge Ecosystem
This microservice is one of the three core pillars of the **TalentBridge** digital recruitment platform:
1. 🖥️ **[TalentBridge-Frontend](https://github.com/AndrewEhab-Tech/TalentBridge-Frontend)** — Modern dual-language web portal for Candidates, Employers, and Admins.
2. ⚙️ **[TalentBridge-Backend](https://github.com/AndrewEhab-Tech/TalentBridge-Backend)** — Core HRMS REST API with 120+ endpoints, MySQL migrations, and Sanctum auth.
3. 🧠 **[TalentBridge-AI-ATS-Service](https://github.com/AndrewEhab-Tech/TalentBridge-AI-ATS-Service)** *(This Repository)* — Local AI semantic resume parsing and scoring engine.

---

## 🎯 Key Features
- **Multi-Format Semantic Parser**: Automated text extraction from PDF (`pdfplumber`, `PyMuPDF`), Word (`python-docx`), and plain text (`.txt`).
- **Comprehensive ATS Scoring**:
  - Semantic Match Score ($0 - 100\%$).
  - Identified Matched Skills vs Missing Requirements.
  - Granular Evaluation of Experience, Seniority, and Education.
  - Objective Strengths & Weaknesses breakdown.
  - Final AI Recommendation (`Strong Match`, `Moderate Match`, `Weak Match`).
- **100% Compatible with Laravel Backend**: Native mapping to the `cv_match_results` and `cv_scans` relational tables.
- **Embedded Interactive Playground**: Built-in web dashboard at `http://127.0.0.1:8000` for live resume uploads and instant evaluation.
- **High-Performance Asynchronous API**: Built on FastAPI with non-blocking worker pools.

---

## 🚀 Quick Start Guide

### 1. Prerequisites
- Python 3.10+
- [Ollama](https://ollama.com/) running locally:
  ```bash
  ollama run llama3.2
  ```

### 2. Installation
```bash
git clone https://github.com/AndrewEhab-Tech/TalentBridge-AI-ATS-Service.git
cd TalentBridge-AI-ATS-Service

# Install required dependencies
pip install -r requirements.txt
```

### 3. Run the Server
Using the bundled launcher:
```cmd
run_ats_server.bat
```
Or manually via terminal:
```bash
python -m uvicorn main:app --host 127.0.0.1 --port 8000 --reload
```

Interactive UI and Swagger Docs:
- **Web UI:** [http://127.0.0.1:8000](http://127.0.0.1:8000)
- **Interactive Swagger Docs:** [http://127.0.0.1:8000/docs](http://127.0.0.1:8000/docs)

---

## 🔌 API Reference (FastAPI)

### 1. Evaluate Resume File
* **Method:** `POST /api/ats/evaluate-file`
* **Content-Type:** `multipart/form-data`
* **Parameters:**
  * `file`: Resume file (`.pdf` or `.docx`)
  * `job_description`: Full text of the target job description
  * `job_title`: (Optional) Target job title

#### Example JSON Response:
```json
{
  "match_score": 87.5,
  "matched_skills": ["Laravel", "PHP", "MySQL", "REST API", "Docker"],
  "missing_skills": ["AWS Lambda", "Kubernetes"],
  "strengths": [
    "Solid 4+ years background in backend API development",
    "Strong relational database schema design skills"
  ],
  "weaknesses": [
    "Limited cloud-native container orchestration experience"
  ],
  "recommendation": "Strong Match"
}
```

---

## 👨‍💻 Author
**Andrew Ehab**  
* GitHub: [@AndrewEhab-Tech](https://github.com/AndrewEhab-Tech)
