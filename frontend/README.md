# TalentBridge — Digital Recruitment & Virtual Career Fair Web Platform

[![Laravel](https://img.shields.io/badge/Laravel-11%20%2F%2012-FF2D20.svg?logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4.svg?logo=php&logoColor=white)](https://php.net)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.4%2B-38B2AC.svg?logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![Vite](https://img.shields.io/badge/Vite-5.0%2B-646CFF.svg?logo=vite&logoColor=white)](https://vitejs.dev)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

A modern, responsive, multi-tenant digital recruitment web platform and virtual career fair experience built with **Laravel** and **Tailwind CSS**. **TalentBridge** serves as a decoupled presentation tier that communicates via authenticated RESTful JSON APIs with the backend ecosystem.

---

## 🌐 The TalentBridge Ecosystem
This web platform is one of the three core pillars of the **TalentBridge** ecosystem:
1. 🖥️ **[TalentBridge-Frontend](https://github.com/AndrewEhab-Tech/TalentBridge-Frontend)** *(This Repository)* — Modern dual-language web portal for Candidates, Employers, and Admins.
2. ⚙️ **[TalentBridge-Backend](https://github.com/AndrewEhab-Tech/TalentBridge-Backend)** — Core HRMS REST API with 120+ endpoints, MySQL migrations, and Sanctum auth.
3. 🧠 **[TalentBridge-AI-ATS-Service](https://github.com/AndrewEhab-Tech/TalentBridge-AI-ATS-Service)** — Local AI semantic resume parsing and scoring engine.

---

## ✨ Key Features & Multi-Persona Portals

### 1. 🎓 Candidate Portal
- **Profile & Resume Hub**: Dynamic skills passport, education history, work experiences, multi-version CV repository.
- **Social Media Integration**: Verified links to LinkedIn, GitHub, Twitter/X, Behance, and portfolio websites with smart domain validation.
- **Smart Job Search**: Filter by location, department, employment type, and salary range.
- **Application Milestone Tracker**: Full transparency from application submission, exam invitation, interview scheduling, to final job offer.
- **Assessment & Examination Engine**: Take pre-screening technical exams directly in the browser.
- **Self-Service Interview Booking**: Pick convenient slots synchronized with interviewers without email ping-pong.

### 2. 🏢 Employer / Company Portal
- **Job Management**: Create, publish, update, and manage job vacancies.
- **Candidate Pipeline**: Filter applicants, inspect resume scores, and update candidate statuses.
- **Exam Builder**: Design technical pre-screening questionnaires and tests.
- **Job Offer Generator**: Issue structured, formal digital job offers.

### 3. 🎙️ Technical Interviewer Portal
- **Availability Management**: Set recurring or custom availability slots.
- **Evaluation Rubrics**: Structured scoring matrices and notes for each interview round.

### 4. 🛡️ Super Admin Portal
- **Dashboard & KPIs**: Comprehensive platform-wide analytics and statistics.
- **Company Verification**: Review and approve/reject newly registered hiring companies.
- **User Management**: Role-Based Access Control (RBAC), user status transitions, and audit logging.

### 5. 🏛️ Virtual Career Fair (Exhibition Hall)
- Immersive interactive visual walkthrough (`/hall/exterior`, `/hall/landing`, `/hall/lobby`, `/hall/exhibit`) connecting job seekers with recruiting company booths.

---

## 📸 Screenshots & Platform Previews

| Landing & Career Fair Hero | Candidate Profile & Social Hub |
| :---: | :---: |
| ![Landing Hero](docs/screenshots/01_Home_Landing.png) | ![Candidate Profile](docs/screenshots/05_Candidate_Profile.png) |

| Multi-Role Login Portal | Application Pipeline & Tracking |
| :---: | :---: |
| ![Login Portal](docs/screenshots/02_Login_Portal.png) | ![Applications Pipeline](docs/screenshots/12_Candidate_Applications_Pipeline.png) |

---

## 🛠️ Architecture & Tech Stack
- **Framework:** Laravel 11.x / 12.x
- **UI / Styling:** Tailwind CSS 3.4+, Vite
- **Localization:** Dual-language engine supporting **English (LTR)** and **Arabic (RTL)** seamlessly.
- **API Client:** Pure API-first architecture using a centralized `CurlClient` and `ApiBase` session abstraction.

---

## 🚀 Getting Started

### 1. Requirements
- PHP 8.2 or 8.5+
- Composer
- Node.js & npm (optional; pre-compiled assets are included in `public/build`)

### 2. Installation
```bash
# Clone the repository
git clone https://github.com/AndrewEhab-Tech/TalentBridge-Frontend.git
cd TalentBridge-Frontend

# Install PHP dependencies
composer install

# Setup environment
cp .env.example .env
php artisan key:generate
```

### 3. Configure Backend Connection
In your `.env` file, point to the live [TalentBridge-Backend](https://github.com/AndrewEhab-Tech/TalentBridge-Backend) API:
```env
API_URL=http://127.0.0.1:8001/api
```

### 4. Run the Development Server
```bash
php artisan serve --port=8080
```
Then visit: 👉 **[http://127.0.0.1:8080](http://127.0.0.1:8080)**

---

## 👨‍💻 Author
**Andrew Ehab**  
* GitHub: [@AndrewEhab-Tech](https://github.com/AndrewEhab-Tech)
