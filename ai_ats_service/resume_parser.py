import io
import re
from pathlib import Path
from typing import Union, Dict, Any, List

def clean_word(w: str) -> str:
    """Strip punctuation and clean single word."""
    return re.sub(r'^[^\w]+|[^\w]+$', '', w).strip()

def extract_pdf_thorough(file_bytes: bytes) -> Dict[str, Any]:
    """
    Robust Multi-Engine PDF Extractor:
    1. PyMuPDF with span inspection for bold keywords.
    2. PyMuPDF direct text fallback for non-standard layout PDFs.
    3. pdfplumber layout and word-by-word fallback.
    4. pypdf fallback.
    5. Table extraction via pdfplumber.
    6. Scanned image detection.
    """
    import pymupdf
    import pdfplumber
    import pypdf
    
    full_text_lines = []
    bold_phrases = []
    engine_used = "PyMuPDF (Bold-Aware)"
    is_scanned = False
    
    # ----------------------------------------------------
    # Stage 1: PyMuPDF Span / Dict Extraction (with Bold)
    # ----------------------------------------------------
    try:
        doc = pymupdf.open(stream=file_bytes, filetype="pdf")
        total_images = 0
        
        for page in doc:
            total_images += len(page.get_images())
            page_dict = page.get_text("dict")
            blocks = page_dict.get("blocks", [])
            page_lines = []
            
            for b in blocks:
                if b.get("type", 0) == 0 and "lines" in b:
                    for line in b["lines"]:
                        line_spans = []
                        for span in line.get("spans", []):
                            text = span.get("text", "")
                            if not text.strip():
                                line_spans.append(text)
                                continue
                                
                            flags = span.get("flags", 0)
                            font_name = span.get("font", "").lower()
                            is_bold = (flags & 16) != 0 or any(k in font_name for k in ["bold", "black", "heavy", "medium", "semibold"])
                            
                            clean_t = text.strip()
                            if is_bold and len(clean_t) > 1:
                                bold_phrases.append(clean_t)
                                line_spans.append(f"**{clean_t}** ")
                            else:
                                line_spans.append(text)
                                
                        block_str = "".join(line_spans).strip()
                        if block_str:
                            page_lines.append(block_str)
                            
            if page_lines:
                full_text_lines.append("\n".join(page_lines))
            else:
                # Page-level fallback within PyMuPDF
                direct_t = page.get_text("text").strip()
                if not direct_t:
                    direct_t = page.get_text("blocks")
                    if isinstance(direct_t, list):
                        direct_t = "\n".join([blk[4].strip() for blk in direct_t if len(blk) > 4 and blk[4].strip()])
                if direct_t:
                    full_text_lines.append(direct_t)
                    
        doc.close()
    except Exception as e:
        print(f"[Warning] PyMuPDF extraction warning: {e}")
        
    full_text = "\n\n".join([l for l in full_text_lines if l.strip()])
    
    # ----------------------------------------------------
    # Stage 2: Fallback to pdfplumber if text is sparse (< 30 chars)
    # ----------------------------------------------------
    if len(full_text.strip()) < 30:
        try:
            with pdfplumber.open(io.BytesIO(file_bytes)) as pdf:
                plumber_pages = []
                for p in pdf.pages:
                    t = p.extract_text(layout=True)
                    if not t or len(t.strip()) < 10:
                        t = p.extract_text(layout=False)
                    if not t or len(t.strip()) < 10:
                        words = p.extract_words()
                        t = " ".join([w["text"] for w in words if "text" in w])
                    if t and t.strip():
                        plumber_pages.append(t.strip())
                if plumber_pages:
                    full_text = "\n\n".join(plumber_pages)
                    engine_used = "pdfplumber"
        except Exception as e:
            print(f"[Warning] pdfplumber fallback warning: {e}")
            
    # ----------------------------------------------------
    # Stage 3: Fallback to pypdf if still sparse
    # ----------------------------------------------------
    if len(full_text.strip()) < 30:
        try:
            reader = pypdf.PdfReader(io.BytesIO(file_bytes))
            pypdf_pages = []
            for p in reader.pages:
                t = p.extract_text()
                if t and t.strip():
                    pypdf_pages.append(t.strip())
            if pypdf_pages:
                full_text = "\n\n".join(pypdf_pages)
                engine_used = "pypdf"
        except Exception as e:
            print(f"[Warning] pypdf fallback warning: {e}")
            
    # ----------------------------------------------------
    # Stage 4: Extract tabular text via pdfplumber
    # ----------------------------------------------------
    try:
        with pdfplumber.open(io.BytesIO(file_bytes)) as pdf:
            table_lines = []
            for page in pdf.pages:
                tables = page.extract_tables()
                for table in tables:
                    for row in table:
                        row_cells = [str(c).strip() for c in row if c and str(c).strip()]
                        if row_cells:
                            row_txt = " | ".join(row_cells)
                            if row_txt not in full_text:
                                table_lines.append(row_txt)
            if table_lines:
                full_text += "\n\n" + "\n".join(table_lines)
    except Exception:
        pass
        
    # Check if this is a scanned PDF (has images but zero text extracted)
    if len(full_text.strip()) < 30:
        try:
            doc = pymupdf.open(stream=file_bytes, filetype="pdf")
            imgs = sum(len(p.get_images()) for p in doc)
            doc.close()
            if imgs > 0:
                is_scanned = True
        except Exception:
            pass

    clean_bold = list(dict.fromkeys([b for b in bold_phrases if len(b) > 1]))
    
    return {
        "text": full_text,
        "bold_keywords": clean_bold,
        "engine": engine_used,
        "is_scanned": is_scanned
    }

def extract_docx_thorough(file_bytes: bytes) -> Dict[str, Any]:
    """
    Extracts text from Word documents (.docx) including:
    - Standard paragraphs
    - Tables
    - Text boxes (w:txbxContent - often used by resume templates)
    - Headers & footers
    """
    import docx
    
    doc = docx.Document(io.BytesIO(file_bytes))
    lines = []
    bold_phrases = []
    
    # 1. Paragraphs
    for p in doc.paragraphs:
        p_parts = []
        for run in p.runs:
            t = run.text
            if not t:
                continue
            clean_t = t.strip()
            if run.bold and len(clean_t) > 1:
                bold_phrases.append(clean_t)
                p_parts.append(f"**{clean_t}** ")
            else:
                p_parts.append(t)
                
        para_str = "".join(p_parts).strip()
        if para_str:
            lines.append(para_str)
            
    # 2. Tables
    for table in doc.tables:
        for row in table.rows:
            row_cells = [cell.text.strip() for cell in row.cells if cell.text.strip()]
            if row_cells:
                lines.append(" | ".join(row_cells))
                
    # 3. Text Boxes (frequently used in Canva / modern CV templates)
    try:
        for txbx in doc._element.xpath('.//w:txbxContent'):
            for p in txbx.xpath('.//w:p'):
                txt = "".join(p.itertext()).strip()
                if txt and txt not in lines:
                    lines.append(txt)
    except Exception:
        pass
        
    # 4. Headers & Footers
    try:
        for section in doc.sections:
            if section.header:
                for p in section.header.paragraphs:
                    if p.text.strip() and p.text.strip() not in lines:
                        lines.append(p.text.strip())
    except Exception:
        pass
        
    clean_bold = list(dict.fromkeys([b for b in bold_phrases if len(b) > 1]))
    
    return {
        "text": "\n".join(lines),
        "bold_keywords": clean_bold,
        "engine": "python-docx (Thorough: Paragraphs + Tables + Textboxes)",
        "is_scanned": False
    }

def analyze_words(text: str) -> Dict[str, Any]:
    """Analyzes word count and vocabulary."""
    raw_words = re.findall(r'\b[A-Za-z0-9_\u0600-\u06FF+#\.-]+\b', text)
    cleaned_words = [clean_word(w) for w in raw_words if clean_word(w)]
    unique_words = sorted(list(set([w.lower() for w in cleaned_words])))
    
    return {
        "total_words": len(cleaned_words),
        "unique_words_count": len(unique_words),
        "char_count": len(text)
    }

def parse_resume(file_bytes: bytes, filename: str) -> Dict[str, Any]:
    """
    Parses resume files detecting all text, word counts, and BOLD keywords.
    """
    ext = Path(filename).suffix.lower()
    
    if ext == ".pdf":
        result = extract_pdf_thorough(file_bytes)
    elif ext in [".docx", ".doc"]:
        try:
            result = extract_docx_thorough(file_bytes)
        except Exception:
            # Fallback if old binary doc or corrupt zip
            text = file_bytes.decode("utf-8", errors="ignore")
            # Filter non-printable binary garbage
            printable = "".join([c for c in text if c.isprintable() or c in "\n\r\t"])
            result = {
                "text": printable,
                "bold_keywords": [],
                "engine": "docx-binary-fallback",
                "is_scanned": False
            }
    elif ext in [".txt", ".md"]:
        text = file_bytes.decode("utf-8", errors="ignore")
        result = {
            "text": text,
            "bold_keywords": [],
            "engine": "plain-text",
            "is_scanned": False
        }
    else:
        raise ValueError(f"Unsupported file format '{ext}'. Allowed: .pdf, .docx, .txt")
        
    stats = analyze_words(result["text"])
    
    return {
        "text": result["text"],
        "bold_keywords": result.get("bold_keywords", []),
        "word_count": stats["total_words"],
        "unique_words_count": stats["unique_words_count"],
        "char_count": stats["char_count"],
        "engine": result["engine"],
        "is_scanned": result.get("is_scanned", False)
    }
