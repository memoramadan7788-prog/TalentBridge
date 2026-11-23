import json
import re
import ollama
from typing import Dict, Any, Optional, List, Tuple

DEFAULT_MODEL = "llama3.2"

SYSTEM_PROMPT = """You are a rigorous, zero-hallucination, domain-agnostic AI Applicant Tracking System (ATS).
Your task is to analyze and compare any Candidate Resume against any Job Description (JD) across any industry or field.

### CRITICAL RULES:
1. EXAMINE ALL SECTIONS COMPLETELY:
   Carefully read all sections of the CV, including Core Skills, Work Experience, Projects, Certifications, and Summary.
2. ABSOLUTELY NO FALSE OMISSIONS:
   If a skill, tool, certification, methodology, or keyword appears anywhere in the CV, it is MATCHED.
   You are STRICTLY FORBIDDEN from putting any skill or competency that is present in the CV into "missing_skills".
3. ONLY GENUINELY ABSENT REQUIREMENTS IN MISSING:
   Only requirements completely unmentioned or clearly absent from the CV (e.g., missing specific industry experience, required licenses, or key domain tools) belong in "missing_skills".
4. DOMAIN ADAPTABILITY:
   Evaluate skills based on the specific industry of the provided JD (e.g., Finance, Healthcare, Marketing, Engineering, Sales, Administration, IT, etc.).
5. CONCISE & FOCUSED SKILL LISTS:
   List the top key matched skills (up to 15) and top critical missing requirements (up to 15). Avoid excessively long redundant itemized lists.
6. LANGUAGE:
   All output text (summary, experience_match, education_match, strengths, weaknesses, recommendation) must be written exclusively in clear, professional English.
7. OUTPUT FORMAT:
   Respond ONLY with valid JSON (no markdown formatting, no commentary):
   {
     "score": <0-100>,
     "matched_skills": [<skills/tools/requirements found in both JD and Resume>],
     "missing_skills": [<skills/tools/requirements required in JD that are genuinely absent in Resume>],
     "experience_match": "<1-2 sentence assessment>",
     "education_match": "<1 sentence assessment>",
     "strengths": [<2-4 strengths>],
     "weaknesses": [<1-4 weaknesses>],
     "summary": "<2-3 sentence assessment>",
     "recommendation": "<Strong Match | Moderate Match | Weak Match>"
   }
"""

def extract_json_from_text(raw_text: str) -> Dict[str, Any]:
    """
    Self-repairing JSON extractor:
    1. Tries standard markdown fences and { ... } bounds.
    2. Auto-closes unclosed strings, arrays, and braces if truncated by token limits.
    3. Resilient regex field extractor fallback so it NEVER throws a JSONDecodeError.
    """
    t = raw_text.strip()
    match = re.search(r"```(?:json)?\s*([\s\S]*?)\s*```", t)
    if match:
        t = match.group(1).strip()
    else:
        start = t.find("{")
        end = t.rfind("}")
        if start != -1 and end != -1:
            candidate = t[start:end+1]
            try:
                return json.loads(candidate)
            except Exception:
                pass
                
    try:
        return json.loads(t)
    except Exception:
        pass
        
    # Auto-repair truncated JSON:
    cleaned = t
    if cleaned.count('"') % 2 != 0:
        cleaned += '"'
    open_brackets = cleaned.count('[') - cleaned.count(']')
    if open_brackets > 0:
        cleaned += '\n' + (']' * open_brackets)
    open_braces = cleaned.count('{') - cleaned.count('}')
    if open_braces > 0:
        cleaned += '\n' + ('}' * open_braces)
        
    try:
        return json.loads(cleaned)
    except Exception:
        pass
        
    # Guaranteed fallback: Regex Field Extractor
    result = {}
    score_m = re.search(r'"score"\s*:\s*(\d+(?:\.\d+)?)', raw_text)
    result["score"] = float(score_m.group(1)) if score_m else 0.0

    matched_m = re.search(r'"matched_skills"\s*:\s*\[([\s\S]*?)(?:\]|\n\s*"[a-zA-Z_]+"\s*:|\Z)', raw_text)
    result["matched_skills"] = re.findall(r'"([^"]+)"', matched_m.group(1)) if matched_m else []

    missing_m = re.search(r'"missing_skills"\s*:\s*\[([\s\S]*?)(?:\]|\n\s*"[a-zA-Z_]+"\s*:|\Z)', raw_text)
    result["missing_skills"] = re.findall(r'"([^"]+)"', missing_m.group(1)) if missing_m else []

    for f in ["experience_match", "education_match", "summary", "recommendation"]:
        fm = re.search(r'"' + f + r'"\s*:\s*"([^"]*)"', raw_text)
        result[f] = fm.group(1) if fm else ""

    str_m = re.search(r'"strengths"\s*:\s*\[([\s\S]*?)(?:\]|\n\s*"[a-zA-Z_]+"\s*:|\Z)', raw_text)
    result["strengths"] = re.findall(r'"([^"]+)"', str_m.group(1)) if str_m else []

    weak_m = re.search(r'"weaknesses"\s*:\s*\[([\s\S]*?)(?:\]|\n\s*"[a-zA-Z_]+"\s*:|\Z)', raw_text)
    result["weaknesses"] = re.findall(r'"([^"]+)"', weak_m.group(1)) if weak_m else []

    return result

def clean_alphanumeric(text: str) -> str:
    """Normalize text by keeping only alphanumeric characters and tech symbols (+, #, .)."""
    return re.sub(r'[^a-zA-Z0-9+#\.]+', ' ', text.lower()).strip()

def is_skill_present_in_cv(skill: str, cv_text: str) -> bool:
    """
    Rigorously checks whether a skill is genuinely present in the candidate CV.
    Strictly forbids false positive single-word matches (e.g. 'Pharmacy software' matching just 'software').
    Supports exact phrases, alphanumeric normalization, common tech aliases, and composite skill sets.
    """
    s_clean = skill.strip().lower()
    if not s_clean or len(s_clean) < 2:
        return False
        
    cv_lower = cv_text.lower()
    cv_norm = " " + re.sub(r'\s+', ' ', cv_lower) + " "
    
    # 1. Exact phrase match
    if f" {s_clean} " in cv_norm or s_clean in cv_norm:
        return True
        
    # 2. Normalized alphanumeric phrase check with boundary spacing
    s_alpha = clean_alphanumeric(s_clean)
    cv_alpha = " " + clean_alphanumeric(cv_norm) + " "
    if f" {s_alpha} " in cv_alpha:
        return True
        
    # 3. Industry & Tech Aliases dictionary
    tech_aliases = {
        "javascript": ["javascript", "js", "es6", "es6+", "ecmascript"],
        "javascript (es6+)": ["javascript", "es6", "es6+", "javascript es6", "js"],
        "typescript": ["typescript", "ts"],
        "python": ["python", "py"],
        "react": ["react", "react.js", "reactjs"],
        "react.js": ["react", "react.js", "reactjs"],
        "reactjs": ["react", "react.js", "reactjs"],
        "node": ["node", "node.js", "nodejs"],
        "node.js": ["node", "node.js", "nodejs"],
        "nodejs": ["node", "node.js", "nodejs"],
        "express": ["express", "express.js", "expressjs"],
        "express.js": ["express", "express.js", "expressjs"],
        "postgresql": ["postgresql", "postgres", "psql"],
        "mongodb": ["mongodb", "mongo"],
        "docker": ["docker", "containerization"],
        "kubernetes": ["kubernetes", "k8s"],
        "rest apis": ["rest api", "rest apis", "restful apis", "restful api", "restful"],
        "restful apis": ["rest api", "rest apis", "restful apis", "restful api", "restful"],
        "git": ["git", "github", "gitlab"],
        "ci/cd": ["ci/cd", "ci cd", "continuous integration", "github actions"],
        "aws": ["aws", "amazon web services"],
        "next.js": ["next", "next.js", "nextjs"],
        "vue": ["vue", "vue.js", "vuejs"],
        "vue.js": ["vue", "vue.js", "vuejs"],
    }
    
    if s_clean in tech_aliases:
        for alias in tech_aliases[s_clean]:
            if f" {alias} " in cv_alpha or f" {alias} " in cv_norm:
                return True

    # 4. Composite parenthesized skills: e.g. "AWS (EC2, S3, Lambda, CloudWatch, IAM, VPC, RDS)"
    paren_match = re.match(r'^([a-zA-Z0-9+#\.]+)\s*\((.*?)\)$', s_clean)
    if paren_match:
        main_tech = paren_match.group(1).strip()
        sub_items = [item.strip() for item in paren_match.group(2).split(',') if item.strip()]
        
        # Primary tech must exist in CV
        if f" {clean_alphanumeric(main_tech)} " not in cv_alpha:
            return False
            
        # And at least half of the sub-items must be in CV
        matched_subs = [item for item in sub_items if f" {clean_alphanumeric(item)} " in cv_alpha]
        if len(matched_subs) >= max(1, len(sub_items) // 2):
            return True

    return False

def post_verify_skills(matched: List[str], missing: List[str], resume_text: str) -> Tuple[List[str], List[str]]:
    """
    Rigorously verifies both matched and missing lists against resume text:
    1. If a skill is in 'missing' but actually exists in CV -> move to 'matched'.
    2. If a skill is in 'matched' but is NOT in CV -> move to 'missing'.
    """
    final_matched = []
    final_missing = list(missing)
    
    # Check matched skills from model: must genuinely be in CV
    for item in matched:
        item_clean = item.strip()
        if not item_clean:
            continue
        if is_skill_present_in_cv(item_clean, resume_text):
            final_matched.append(item_clean)
        else:
            final_missing.append(item_clean)
            
    # Check missing skills from model: if genuinely in CV, promote to matched
    promoted_to_matched = []
    remaining_missing = []
    for item in final_missing:
        item_clean = item.strip()
        if not item_clean:
            continue
        if is_skill_present_in_cv(item_clean, resume_text):
            promoted_to_matched.append(item_clean)
        else:
            remaining_missing.append(item_clean)
            
    final_matched.extend(promoted_to_matched)
    
    clean_matched = list(dict.fromkeys(final_matched))
    clean_missing = list(dict.fromkeys([m for m in remaining_missing if m not in clean_matched]))
    
    return clean_matched, clean_missing

def evaluate_cv(
    resume_text: str,
    job_description: str,
    job_title: Optional[str] = None,
    bold_keywords: Optional[List[str]] = None,
    model_name: str = DEFAULT_MODEL
) -> Dict[str, Any]:
    """
    Evaluate a resume against a job description with zero-hallucination verification.
    """
    title_context = f"\nJob Title: {job_title}\n" if job_title else ""
    bold_context = ""
    if bold_keywords:
        bold_context = f"\n### CANDIDATE HIGHLIGHTED/BOLD SKILLS & HEADINGS:\n{', '.join(bold_keywords[:50])}\n"
    
    user_prompt = f"""{title_context}
### JOB DESCRIPTION REQUIREMENTS:
{job_description}
{bold_context}
### FULL CANDIDATE RESUME TEXT:
{resume_text}

Perform the factual audit now.
"""

    res = ollama.generate(
        model=model_name,
        system=SYSTEM_PROMPT,
        prompt=user_prompt,
        format="json",
        stream=False,
        options={
            "temperature": 0.1,
            "top_p": 0.9,
            "num_predict": 2048,
            "num_ctx": 4096
        }
    )

    raw_response = res["response"] if isinstance(res, dict) else res.response
    result = extract_json_from_text(raw_response)

    raw_matched = result.get("matched_skills", [])
    raw_missing = result.get("missing_skills", [])
    
    # Run the bulletproof post-verification layer
    verified_matched, verified_missing = post_verify_skills(raw_matched, raw_missing, resume_text)

    raw_score = float(result.get("score", 0))
    total_skills = len(verified_matched) + len(verified_missing)
    if total_skills > 0:
        if len(verified_matched) == 0:
            score = 0.0
        else:
            skills_ratio = len(verified_matched) / total_skills
            score = (raw_score * 0.3) + (skills_ratio * 100 * 0.7)
    else:
        score = raw_score
        
    score = max(0.0, min(100.0, score))

    # Determine recommendation based on score
    if score >= 75:
        rec = "Strong Match"
    elif score >= 50:
        rec = "Moderate Match"
    else:
        rec = "Weak Match"

    return {
        "score": round(score, 2),
        "matched_skills": verified_matched,
        "missing_skills": verified_missing,
        "experience_match": result.get("experience_match", ""),
        "education_match": result.get("education_match", ""),
        "strengths": result.get("strengths", []),
        "weaknesses": result.get("weaknesses", []),
        "summary": result.get("summary", ""),
        "recommendation": rec,
        "model_version": f"ollama:{model_name}",
        "run_status": "ok"
    }
