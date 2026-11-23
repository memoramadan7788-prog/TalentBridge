import re

cv_text = """
• AWS Certified Developer – Associate

TECHNICAL SKILLS
— Frontend: React.js, Next.js, JavaScript (ES6+), TypeScript, HTML5, CSS3, Tailwind CSS / Bootstrap |
Backend: Node.js, Express.js, RESTful APIs | Database: MongoDB, MySQL, PostgreSQL, Redis Caching |
Cloud & DevOps: AWS (EC2, S3, Lambda, CloudWatch, IAM, VPC, RDS), Docker, CI/CD, Terraform, CloudFormation |
Security: JWT, OAuth2, DevSecOps Practices

PROJECTS
Task Management Application: Built a full-stack task management web app using ASP.NET Core
"""

missing_reported = [
    "SaaS products",
    "Clean Architecture",
    "AI or large language models",
    "Integrations with third-party platforms",
    "JavaScript (ES6+)",
    "AWS (EC2, S3, Lambda, CloudWatch, IAM, VPC, RDS)"
]

def normalize_text(text: str) -> str:
    # Replace newlines, multiple spaces, punctuation variations
    t = text.lower()
    t = re.sub(r'[\r\n\t]+', ' ', t)
    t = re.sub(r'\s+', ' ', t)
    return t

norm_cv = normalize_text(cv_text)

print("--- Testing Substring Matching ---")
for item in missing_reported:
    norm_item = normalize_text(item)
    # 1. Exact normalized substring
    exact_match = norm_item in norm_cv
    
    # 2. Key words extraction
    # Strip brackets, parentheses, punctuation
    words = re.findall(r'[a-zA-Z0-9+#\.]+', norm_item)
    # Check if core tokens match
    matched_words = [w for w in words if w in norm_cv]
    ratio = len(matched_words) / len(words) if words else 0
    
    print(f"Item: '{item}'")
    print(f"  Exact substring: {exact_match}")
    print(f"  Words ratio: {len(matched_words)}/{len(words)} ({ratio*100:.1f}%) -> {matched_words[:5]}")
