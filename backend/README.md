# TalentBridge — Core HRMS & Digital Recruitment REST API

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20.svg?logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4.svg?logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-4479A1.svg?logo=mysql&logoColor=white)](https://mysql.com)
[![Sanctum](https://img.shields.io/badge/Laravel-Sanctum-red.svg)](https://laravel.com/docs/sanctum)
[![OpenAPI](https://img.shields.io/badge/OpenAPI-3.0%20(Scramble)-green.svg?logo=openapi-initiative&logoColor=white)](https://scramble.dedoc.co)
[![License](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)

An enterprise-grade, multi-tenant digital recruitment ecosystem and Human Resource Management System (HRMS) RESTful API. **TalentBridge Backend** powers the end-to-end recruitment lifecycle: candidate profiles, multi-platform social media integration, job marketplaces, technical pre-screening assessment exams, self-service interview booking, digital job offers, and administrative RBAC governance with **120+ RESTful API endpoints**.

---

## 🌐 The TalentBridge Ecosystem
This backend API is one of the three core pillars of the **TalentBridge** ecosystem:
1. ⚙️ **[TalentBridge-Backend](https://github.com/AndrewEhab-Tech/TalentBridge-Backend)** *(This Repository)* — Core HRMS REST API with 120+ endpoints, MySQL migrations, and Sanctum auth.
2. 🖥️ **[TalentBridge-Frontend](https://github.com/AndrewEhab-Tech/TalentBridge-Frontend)** — Modern dual-language web portal for Candidates, Employers, and Admins.
3. 🧠 **[TalentBridge-AI-ATS-Service](https://github.com/AndrewEhab-Tech/TalentBridge-AI-ATS-Service)** — Local AI semantic resume parsing and scoring engine.

---

## ⚡ Core Architectural Capabilities

### 1. 🛡️ Identity & Access Governance (RBAC)
- Multi-persona role isolation powered by **Laravel Sanctum** bearer tokens:
  - `candidate`: Personal profile, resume locker, job discovery, exams, applications, interview self-service.
  - `company`: Enterprise recruitment management, job postings, talent pool search, exam creation, offer letters.
  - `interviewer`: Company engineers managing availability slots and conducting structured evaluations.
  - `admin`: Super-admin dashboard, platform audit trails, company approvals/suspensions, user lifecycle management.

### 2. 🌐 Multi-Platform Social Media Integration
- Normalized social media catalog supporting 9 major networks (LinkedIn, GitHub, X/Twitter, Behance, Dribbble, Instagram, YouTube, Facebook, Portfolio).
- **Smart Domain Validation**: Backend enforces strict domain compliance (e.g. verifying `linkedin.com` URLs for LinkedIn links) returning clean `422 Unprocessable Content` on mismatch.

### 3. 📝 Dynamic Assessment & Exam Engine
- Create pre-screening technical exams linked directly to job applications.
- Timed multiple-choice and open questions with automated grading and result tracking.

### 4. 📅 Zero-Friction Interview Booking
- Interviewers publish custom or recurring availability slots.
- Candidates select open slots with automatic collision prevention and status tracking.

### 5. 🤖 AI-Powered ATS & Resume Parsing Integration
- Seamless webhook/REST integration with the local [TalentBridge-AI-ATS-Service](https://github.com/AndrewEhab-Tech/TalentBridge-AI-ATS-Service).
- Candidate CV parsing, semantic match scoring ($0 - 100\%$), and automated job title suggestion workflows (`cv_job_title_suggestions`).

### 6. 📖 Interactive OpenAPI 3.0 Documentation
- Powered by Scramble at `/docs/api` with live browser request execution.

---

## 🗄️ Database Architecture
- **55+ Normalized Relational Tables** with foreign keys, compound indexes, and cascades.
- Comprehensive database migration files covering:
  - User identity, tokens, and audit trails.
  - Candidate profiles, resumes, educations, experiences, skills, and social links.
  - Companies, locations, employees, workspaces, and company social profiles.
  - Jobs, job categories, employment types, and screening questions.
  - Exams, questions, choices, and attempt records.
  - Interviews, slots, bookings, and interviewer evaluations.
  - Job offers, approval workflows, and templates.

---

## 🚀 Quick Setup & Installation

### 1. Requirements
- PHP 8.2+ or 8.5+
- Composer 2+
- MySQL 8.0+ or MariaDB

### 2. Installation
```bash
# Clone the repository
git clone https://github.com/AndrewEhab-Tech/TalentBridge-Backend.git
cd TalentBridge-Backend

# Install dependencies
composer install

# Configure environment
cp .env.example .env
php artisan key:generate
```

### 3. Database Migration & Seeding
Configure your MySQL database credentials in `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=recruitment_platform
DB_USERNAME=root
DB_PASSWORD=
```
Then run the master migration and seeders:
```bash
php artisan migrate:fresh --seed
```

### 4. Start the API Server
```bash
php artisan serve --port=8001
```
The API is now live at: 👉 **[http://127.0.0.1:8001/api](http://127.0.0.1:8001/api)**  
Interactive Swagger/OpenAPI Docs: 👉 **[http://127.0.0.1:8001/docs/api](http://127.0.0.1:8001/docs/api)**

---

## 🔑 Pre-Seeded Demonstration Accounts

| Role | Email | Password |
| :--- | :--- | :--- |
| **Super Admin** | `admin@example.com` | `ChangeMe123!` |
| **Company Recruiter** | `company@example.com` | `Password123!` |
| **Job Candidate** | `candidate@example.com` | `Password123!` |
| **Technical Interviewer** | `interviewer@example.com` | `Password123!` |

---

## 📡 API Inventory Overview

| Module | Endpoints Count | Key Routes |
| :--- | :---: | :--- |
| **Authentication** | 6 | `POST /api/auth/register/*`, `POST /api/auth/login`, `GET /api/auth/me` |
| **Candidate Portal** | 22 | `GET/PUT /api/candidate/profile`, `/resume`, `/skills`, `/social-links`, `/applications` |
| **Company Portal** | 18 | `GET/PUT /api/company/profile`, `/jobs`, `/applications`, `/exams`, `/offers` |
| **Interviewer** | 8 | `GET/POST /api/interviewer/availability`, `/interviews`, `/evaluations` |
| **Super Admin** | 12 | `GET /api/admin/dashboard`, `/users`, `/companies/{id}/approve`, `/audit-logs` |
| **Jobs Marketplace** | 6 | `GET /api/jobs`, `GET /api/jobs/{id}`, `GET /api/jobs/search` |
| **Social Media** | 5 | `GET /api/social-media-platforms`, `GET/POST/DELETE /api/candidate/social-links` |
| **Lookups** | 8 | `/countries`, `/cities`, `/categories`, `/skills`, `/degrees` |

---

## 👨‍💻 Author
**Andrew Ehab**  
* GitHub: [@AndrewEhab-Tech](https://github.com/AndrewEhab-Tech)
