import json
from ats_engine import evaluate_cv

cv_from_image = """
• AWS Certified Developer – Associate

TECHNICAL SKILLS
— Frontend: React.js, Next.js, JavaScript (ES6+), TypeScript, HTML5, CSS3, Tailwind CSS / Bootstrap |
Backend: Node.js, Express.js, RESTful APIs | Database: MongoDB, MySQL, PostgreSQL, Redis
Caching | Cloud & DevOps: AWS (EC2, S3, Lambda, CloudWatch, IAM, VPC, RDS), Docker, CI/CD,
Terraform, CloudFormation | Security: JWT, OAuth2, DevSecOps Practices

PROJECTS
Task Management Application: Built a full-stack task management web app using ASP.NET Core
"""

jd_from_image = """
Requirements:
- SaaS products
- Clean Architecture
- AI or large language models
- Integrations with third-party platforms
- JavaScript (ES6+)
- AWS (EC2, S3, Lambda, CloudWatch, IAM, VPC, RDS)
"""

print("Running test with user's exact CV and JD from screenshot...")
res = evaluate_cv(cv_from_image, jd_from_image)

print("\n--- MATCHED SKILLS ---")
print(res["matched_skills"])

print("\n--- MISSING SKILLS ---")
print(res["missing_skills"])

print(f"\nScore: {res['score']}%")
