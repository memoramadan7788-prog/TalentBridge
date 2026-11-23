import pymupdf
import docx
import io
import re
from pathlib import Path
from typing import Union, Dict, Any, List

def extract_pdf_with_bold_formatting(file_bytes_or_path: Union[bytes, str, Path]) -> Dict[str, Any]:
    """
    Extracts text from PDF while explicitly identifying and preserving BOLD words/phrases.
    Returns:
      - text: Full text with bold words wrapped in **bold**
      - bold_keywords: List of all words and phrases that were formatted in bold
    """
    if isinstance(file_bytes_or_path, bytes):
        doc = pymupdf.open(stream=file_bytes_or_path, filetype="pdf")
    else:
        doc = pymupdf.open(str(file_bytes_or_path))
        
    full_text_lines = []
    bold_phrases = []
    
    for page in doc:
        page_dict = page.get_text("dict")
        blocks = page_dict.get("blocks", [])
        
        for b in blocks:
            if "lines" in b:
                block_lines = []
                for line in b["lines"]:
                    line_spans = []
                    for span in line.get("spans", []):
                        text = span.get("text", "")
                        if not text.strip():
                            line_spans.append(text)
                            continue
                            
                        # Check bold flags: flag 16 (bit 4) is bold, or font name contains bold
                        is_bold = (span.get("flags", 0) & 16) != 0 or "bold" in span.get("font", "").lower()
                        
                        if is_bold:
                            clean_t = text.strip()
                            if len(clean_t) > 1:
                                bold_phrases.append(clean_t)
                            line_spans.append(f"**{clean_t}** ")
                        else:
                            line_spans.append(text)
                            
                    block_lines.append("".join(line_spans).strip())
                    
                full_text_lines.append("\n".join(block_lines))
                
    doc.close()
    
    full_text = "\n\n".join([l for l in full_text_lines if l.strip()])
    
    # Deduplicate bold phrases
    clean_bold = list(dict.fromkeys([b for b in bold_phrases if len(b) > 1]))
    
    return {
        "text": full_text,
        "bold_keywords": clean_bold
    }

def extract_docx_with_bold_formatting(file_bytes_or_path: Union[bytes, str, Path]) -> Dict[str, Any]:
    """
    Extracts text from Word documents while explicitly identifying bold words/phrases.
    """
    stream = io.BytesIO(file_bytes_or_path) if isinstance(file_bytes_or_path, bytes) else file_bytes_or_path
    doc = docx.Document(stream)
    
    lines = []
    bold_phrases = []
    
    for p in doc.paragraphs:
        p_text_parts = []
        for run in p.runs:
            t = run.text
            if not t:
                continue
            if run.bold:
                clean_t = t.strip()
                if len(clean_t) > 1:
                    bold_phrases.append(clean_t)
                p_text_parts.append(f"**{clean_t}** ")
            else:
                p_text_parts.append(t)
        
        para_str = "".join(p_text_parts).strip()
        if para_str:
            lines.append(para_str)
            
    # Tables
    for table in doc.tables:
        for row in table.rows:
            row_parts = []
            for cell in row.cells:
                cell_text = cell.text.strip()
                if cell_text:
                    row_parts.append(cell_text)
            if row_parts:
                lines.append(" | ".join(row_parts))
                
    clean_bold = list(dict.fromkeys([b for b in bold_phrases if len(b) > 1]))
    
    return {
        "text": "\n".join(lines),
        "bold_keywords": clean_bold
    }
