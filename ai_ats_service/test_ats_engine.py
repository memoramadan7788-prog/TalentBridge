import json
from ats_engine import evaluate_cv

sample_jd = """
Job Title: Senior Backend Developer (PHP / Laravel)
Requirements:
- 4+ years of backend development experience.
- Strong proficiency in PHP and Laravel framework.
- Experience with MySQL, Redis, Docker, and REST APIs.
- Understanding of microservices and queue systems.
- Bachelor's degree in Computer Science or related field.
"""

sample_cv = """
John Doe - Full Stack Developer
Email: john@example.com

Summary:
Developer with 3 years of experience in PHP, Laravel, and MySQL. Built several web applications and REST APIs.

Skills:
PHP, Laravel, MySQL, Git, HTML, CSS, JavaScript, Basic Docker.

Experience:
Web Developer at TechCorp (2022 - Present)
- Developed APIs using Laravel and MySQL.
- Containerized development environments using Docker.
- Collaborated with frontend developers on RESTful endpoints.

Education:
B.Sc. in Computer Science - University of Science (2021)
"""

print("Running ATS Evaluation with Ollama (Llama 3.2)...")
result = evaluate_cv(
    resume_text=sample_cv,
    job_description=sample_jd,
    job_title="Senior Backend Developer"
)

print("\n--- ATS RESULT ---")
print(json.dumps(result, indent=2, ensure_ascii=False))
