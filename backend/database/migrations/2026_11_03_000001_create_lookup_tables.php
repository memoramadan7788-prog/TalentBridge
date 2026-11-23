<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Normalization step 1/3 - reference ("lookup") tables.
 *
 * Every repeated enum / free-text value that used to live as a string column in many tables
 * becomes a row in ONE table, so filters/joins use integer foreign keys instead of string compares.
 */
return new class extends Migration
{
    /** table => [[code, name, sort_order], ...] */
    private array $static = ["account_types" => [["candidate", "Candidate", 1], ["company", "Company", 2], ["admin", "Admin", 3]], "languages" => [["ar", "العربية", 1], ["en", "English", 2]], "currencies" => [["EGP", "Egyptian Pound", 1], ["USD", "US Dollar", 2], ["EUR", "Euro", 3], ["SAR", "Saudi Riyal", 4], ["AED", "UAE Dirham", 5], ["GBP", "Pound Sterling", 6]], "employment_types" => [["full_time", "Full Time", 1], ["part_time", "Part Time", 2], ["contract", "Contract", 3], ["internship", "Internship", 4], ["freelance", "Freelance", 5], ["temporary", "Temporary", 6]], "work_modes" => [["onsite", "Onsite", 1], ["remote", "Remote", 2], ["hybrid", "Hybrid", 3]], "salary_periods" => [["hour", "Hour", 1], ["day", "Day", 2], ["week", "Week", 3], ["month", "Month", 4], ["year", "Year", 5]], "experience_levels" => [["intern", "Intern", 1], ["junior", "Junior", 2], ["mid", "Mid", 3], ["senior", "Senior", 4], ["lead", "Lead", 5], ["executive", "Executive", 6]], "skill_types" => [["soft", "Soft", 1], ["tech", "Tech", 2]], "skill_levels" => [["beginner", "Beginner", 1], ["intermediate", "Intermediate", 2], ["advanced", "Advanced", 3], ["expert", "Expert", 4]], "social_platforms" => [["linkedin", "Linkedin", 1], ["github", "Github", 2], ["twitter", "Twitter", 3], ["facebook", "Facebook", 4], ["instagram", "Instagram", 5], ["behance", "Behance", 6], ["website", "Website", 7], ["other", "Other", 8]], "document_types" => [["commercial_register", "Commercial Register", 1], ["tax_card", "Tax Card", 2], ["license", "License", 3], ["other", "Other", 4]], "company_sizes" => [["1-10", "1-10", 1], ["11-50", "11-50", 2], ["51-200", "51-200", 3], ["201-500", "201-500", 4], ["501-1000", "501-1000", 5], ["1000+", "1000+", 6]], "company_approval_statuses" => [["pending", "Pending", 1], ["approved", "Approved", 2], ["rejected", "Rejected", 3], ["suspended", "Suspended", 4]], "company_employee_statuses" => [["pending", "Pending", 1], ["approved", "Approved", 2], ["rejected", "Rejected", 3], ["removed", "Removed", 4]], "invitation_statuses" => [["pending", "Pending", 1], ["accepted", "Accepted", 2], ["expired", "Expired", 3], ["revoked", "Revoked", 4]], "job_statuses" => [["draft", "Draft", 1], ["published", "Published", 2], ["closed", "Closed", 3], ["archived", "Archived", 4]], "private_apply_modes" => [["all_employees", "All Employees", 1], ["nominated_only", "Nominated Only", 2]], "hiring_models" => [["direct", "Direct", 1], ["agency", "Agency", 2]], "hiring_team_roles" => [["requisition_owner", "Requisition Owner", 1], ["hiring_manager", "Hiring Manager", 2], ["recruiter", "Recruiter", 3], ["interviewer", "Interviewer", 4], ["member", "Member", 5]], "application_statuses" => [["submitted", "Submitted", 1], ["under_review", "Under Review", 2], ["shortlisted", "Shortlisted", 3], ["exam", "Exam", 4], ["interviewing", "Interviewing", 5], ["offer", "Offer", 6], ["hired", "Hired", 7], ["rejected", "Rejected", 8], ["withdrawn", "Withdrawn", 9]], "application_decisions" => [["hire", "Hire", 1], ["reject", "Reject", 2], ["hold", "Hold", 3]], "application_sources" => [["platform", "Platform", 1], ["agency", "Agency", 2], ["referral", "Referral", 3], ["talent_pool", "Talent Pool", 4], ["direct", "Direct", 5]], "talent_relationships" => [["former_applicant", "Former Applicant", 1], ["referral", "Referral", 2], ["silver_medalist", "Silver Medalist", 3], ["sourced", "Sourced", 4], ["other", "Other", 5]], "interview_types" => [["onsite", "Onsite", 1], ["online", "Online", 2], ["custom", "Custom", 3]], "interview_statuses" => [["pending", "Pending", 1], ["scheduled", "Scheduled", 2], ["confirmed", "Confirmed", 3], ["rescheduled", "Rescheduled", 4], ["completed", "Completed", 5], ["cancelled", "Cancelled", 6], ["no_show", "No Show", 7]], "booking_statuses" => [["booked", "Booked", 1], ["confirmed", "Confirmed", 2], ["completed", "Completed", 3], ["cancelled", "Cancelled", 4]], "availability_statuses" => [["open", "Open", 1], ["booked", "Booked", 2], ["cancelled", "Cancelled", 3]], "offer_statuses" => [["sent", "Sent", 1], ["accepted", "Accepted", 2], ["rejected", "Rejected", 3], ["expired", "Expired", 4]], "offer_approval_statuses" => [["not_required", "Not Required", 1], ["pending", "Pending", 2], ["approved", "Approved", 3], ["rejected", "Rejected", 4]], "exam_statuses" => [["draft", "Draft", 1], ["published", "Published", 2], ["archived", "Archived", 3]], "question_types" => [["mcq", "Mcq", 1], ["written", "Written", 2], ["voice", "Voice", 3], ["coding", "Coding", 4]], "question_sources" => [["manual", "Manual", 1], ["ai", "Ai", 2]], "question_statuses" => [["draft", "Draft", 1], ["published", "Published", 2], ["approved", "Approved", 3], ["rejected", "Rejected", 4]], "attempt_statuses" => [["in_progress", "In Progress", 1], ["submitted", "Submitted", 2], ["graded", "Graded", 3], ["expired", "Expired", 4]], "grading_methods" => [["auto", "Auto", 1], ["ai", "Ai", 2], ["manual", "Manual", 3]], "grading_statuses" => [["pending", "Pending", 1], ["reviewed", "Reviewed", 2], ["final", "Final", 3]], "proctoring_event_types" => [["face_not_detected", "Face Not Detected", 1], ["multiple_faces", "Multiple Faces", 2], ["tab_switch", "Tab Switch", 3], ["looking_away", "Looking Away", 4], ["phone_detected", "Phone Detected", 5], ["audio_detected", "Audio Detected", 6]], "proctoring_decisions" => [["dismissed", "Dismissed", 1], ["confirmed", "Confirmed", 2]], "cv_run_statuses" => [["pending", "Pending", 1], ["ok", "Ok", 2], ["failed", "Failed", 3], ["timeout", "Timeout", 4]], "review_decisions" => [["accept", "Accept", 1], ["override", "Override", 2]], "calendar_providers" => [["google", "Google", 1], ["outlook", "Outlook", 2]], "sync_statuses" => [["pending", "Pending", 1], ["synced", "Synced", 2], ["failed", "Failed", 3]], "notification_channels" => [["email", "Email", 1], ["platform", "Platform", 2]], "notification_types" => [], "delivery_statuses" => [["pending", "Pending", 1], ["sent", "Sent", 2], ["delivered", "Delivered", 3], ["failed", "Failed", 4]], "chatbot_audiences" => [["candidate", "Candidate", 1], ["admin", "Admin", 2], ["company", "Company", 3]], "chatbot_sender_types" => [["user", "User", 1], ["bot", "Bot", 2]], "contact_message_statuses" => [["new", "New", 1], ["in_progress", "In Progress", 2], ["resolved", "Resolved", 3], ["closed", "Closed", 4]], "engagement_statuses" => [["draft", "Draft", 1], ["active", "Active", 2], ["paused", "Paused", 3], ["completed", "Completed", 4], ["cancelled", "Cancelled", 5]], "submission_statuses" => [["pending_review", "Pending Review", 1], ["approved", "Approved", 2], ["rejected", "Rejected", 3]], "profile_visibility_levels" => [["public", "Public", 1], ["employers_only", "Employers Only", 2], ["private", "Private", 3]], "salary_sources" => [["manual", "Manual", 1], ["market_benchmark_2026", "Market Benchmark 2026", 2], ["platform", "Platform", 3], ["imported", "Imported", 4]], "screening_question_types" => [["text", "Text", 1], ["textarea", "Textarea", 2], ["yes_no", "Yes No", 3], ["single_choice", "Single Choice", 4], ["multi_choice", "Multi Choice", 5], ["number", "Number", 6], ["file", "File", 7]]];

    /** open-ended lookups keyed by a unique name; filled from existing data in step 2 */
    private array $dynamic = ["industries", "job_titles", "benefits", "tags", "timezones"];

    public function up(): void
    {
        foreach ($this->static as $table => $rows) {
            Schema::create($table, function (Blueprint $t) use ($table) {
                $t->id();
                $t->string('code', 100)->unique();
                $t->string('name');
                if ($table === 'currencies') {
                    $t->string('symbol', 8)->nullable();
                }
                $t->unsignedSmallInteger('sort_order')->default(0);
                $t->timestamps();
            });

            foreach ($rows as [$code, $name, $order]) {
                DB::table($table)->insert([
                    'code' => $code, 'name' => $name, 'sort_order' => $order,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }

        foreach ($this->dynamic as $table) {
            Schema::create($table, function (Blueprint $t) {
                $t->id();
                $t->string('name')->unique();
                $t->timestamps();
            });
        }

        // departments belong to a company (the same name can exist in two companies)
        Schema::create('departments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $t->string('name');
            $t->timestamps();
            $t->unique(['company_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
        foreach (array_reverse($this->dynamic) as $table) {
            Schema::dropIfExists($table);
        }
        foreach (array_reverse(array_keys($this->static)) as $table) {
            Schema::dropIfExists($table);
        }
    }
};
