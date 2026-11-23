<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Normalization step 2/3 - move data into the lookup tables and into proper child tables.
 *
 * Each step: add FK column -> insert any legacy value that is not in the lookup yet (no data is lost)
 * -> backfill -> (NOT NULL if the old column was) -> drop the old string/JSON column.
 *
 * Not reversible: take a DB backup first.
 */
return new class extends Migration
{
    /** [table, legacy column, lookup table, new FK, required, default code] */
    private array $conversions = [["users", "account_type", "account_types", "account_type_id", true, "candidate"], ["users", "locale", "languages", "language_id", true, "ar"], ["companies", "status", "company_approval_statuses", "company_approval_status_id", true, "pending"], ["companies", "industry", "industries", "industry_id", false, null], ["companies", "size", "company_sizes", "company_size_id", false, null], ["companies", "timezone", "timezones", "timezone_id", false, null], ["company_social_links", "platform", "social_platforms", "social_platform_id", true, null], ["candidate_social_links", "platform", "social_platforms", "social_platform_id", true, null], ["company_documents", "type", "document_types", "document_type_id", true, "other"], ["company_employees", "status", "company_employee_statuses", "company_employee_status_id", true, "pending"], ["company_employees", "requested_job_title", "job_titles", "requested_job_title_id", false, null], ["company_invitations", "status", "invitation_statuses", "invitation_status_id", true, "pending"], ["jobs", "status", "job_statuses", "job_status_id", true, "draft"], ["jobs", "work_mode", "work_modes", "work_mode_id", true, "onsite"], ["jobs", "employment_type", "employment_types", "employment_type_id", true, "full_time"], ["jobs", "salary_period", "salary_periods", "salary_period_id", true, "month"], ["jobs", "currency", "currencies", "currency_id", true, "EGP"], ["jobs", "private_apply_mode", "private_apply_modes", "private_apply_mode_id", false, null], ["jobs", "hiring_model", "hiring_models", "hiring_model_id", true, "direct"], ["offers", "currency", "currencies", "currency_id", true, "EGP"], ["offers", "salary_period", "salary_periods", "salary_period_id", true, "month"], ["offers", "status", "offer_statuses", "offer_status_id", true, "sent"], ["offers", "employment_type", "employment_types", "employment_type_id", false, null], ["offers", "work_model", "work_modes", "work_mode_id", false, null], ["offers", "approval_status", "offer_approval_statuses", "offer_approval_status_id", true, "not_required"], ["salary_records", "currency", "currencies", "currency_id", true, "EGP"], ["salary_records", "job_title", "job_titles", "job_title_id", true, null], ["salary_records", "experience_level", "experience_levels", "experience_level_id", true, null], ["salary_records", "source", "salary_sources", "salary_source_id", true, null], ["salary_recommendations", "currency", "currencies", "currency_id", true, "EGP"], ["applications", "status", "application_statuses", "application_status_id", true, "submitted"], ["applications", "source", "application_sources", "application_source_id", false, null], ["applications", "decision", "application_decisions", "application_decision_id", false, null], ["application_status_history", "from_status", "application_statuses", "from_application_status_id", false, null], ["application_status_history", "to_status", "application_statuses", "to_application_status_id", true, null], ["candidate_experience", "job_title", "job_titles", "job_title_id", true, null], ["skills", "type", "skill_types", "skill_type_id", true, "soft"], ["candidate_skills", "level", "skill_levels", "skill_level_id", false, null], ["candidate_preferences", "employment_type", "employment_types", "employment_type_id", false, null], ["candidate_preferences", "work_model", "work_modes", "work_model_id", false, null], ["candidate_privacy_settings", "visibility", "profile_visibility_levels", "profile_visibility_level_id", true, "employers_only"], ["job_interview_rounds", "interview_type", "interview_types", "interview_type_id", true, "onsite"], ["interviews", "interview_type", "interview_types", "interview_type_id", true, "onsite"], ["interviews", "status", "interview_statuses", "interview_status_id", true, "pending"], ["interviewer_availability", "availability_status", "availability_statuses", "availability_status_id", true, "open"], ["interview_bookings", "booking_status", "booking_statuses", "booking_status_id", true, "booked"], ["exams", "status", "exam_statuses", "exam_status_id", true, "draft"], ["questions", "question_type", "question_types", "question_type_id", true, "mcq"], ["questions", "source", "question_sources", "question_source_id", true, "manual"], ["questions", "status", "question_statuses", "question_status_id", true, "draft"], ["exam_attempts", "status", "attempt_statuses", "attempt_status_id", true, "in_progress"], ["grading_results", "grading_method", "grading_methods", "grading_method_id", true, null], ["grading_results", "status", "grading_statuses", "grading_status_id", true, "pending"], ["proctoring_events", "event_type", "proctoring_event_types", "proctoring_event_type_id", true, null], ["proctoring_reviews", "decision", "proctoring_decisions", "proctoring_decision_id", true, null], ["cv_match_results", "run_status", "cv_run_statuses", "cv_run_status_id", true, "pending"], ["ai_review_decisions", "decision", "review_decisions", "review_decision_id", true, "accept"], ["calendar_integrations", "provider", "calendar_providers", "calendar_provider_id", true, "google"], ["calendar_integrations", "sync_status", "sync_statuses", "sync_status_id", false, null], ["notifications", "channel", "notification_channels", "notification_channel_id", true, "email"], ["notifications", "type", "notification_types", "notification_type_id", true, null], ["notifications", "delivery_status", "delivery_statuses", "delivery_status_id", true, "pending"], ["chatbot_sessions", "audience", "chatbot_audiences", "chatbot_audience_id", true, "candidate"], ["chatbot_messages", "sender_type", "chatbot_sender_types", "chatbot_sender_type_id", true, null], ["contact_messages", "status", "contact_message_statuses", "contact_message_status_id", true, "new"], ["job_screening_questions", "type", "screening_question_types", "screening_question_type_id", true, "text"], ["job_hiring_team", "role", "hiring_team_roles", "hiring_team_role_id", true, "member"], ["company_talent_pool", "source", "application_sources", "application_source_id", false, null], ["company_talent_pool", "relationship", "talent_relationships", "talent_relationship_id", false, null], ["agency_engagements", "status", "engagement_statuses", "engagement_status_id", true, "draft"], ["agency_engagement_submissions", "status", "submission_statuses", "submission_status_id", true, "pending_review"]];

    private array $nameKeyed = ["benefits", "cities", "industries", "job_titles", "tags", "timezones"];

    public function up(): void
    {
        $this->createChildTables();

        // --- departments & cities need extra context, so they run before the generic loop ---
        $this->convertDepartments('jobs', 'department', 'department_id');
        $this->convertDepartments('company_employees', 'requested_department', 'requested_department_id');
        $this->convertCities('jobs', 'location', 'city_id');
        $this->convertCities('candidate_profiles', 'location', 'city_id');
        $this->convertCities('candidate_preferences', 'location', 'city_id');
        $this->convertCities('salary_records', 'location', 'city_id');

        // --- generic string -> lookup FK ---
        foreach ($this->conversions as [$table, $old, $lookup, $new, $required, $default]) {
            $this->convert($table, $old, $lookup, $new, (bool) $required, $default);
        }

        // --- JSON columns -> real tables ---
        $this->jsonList('jobs', 'benefits', 'job_benefits', 'job_id', 'benefits', 'benefit_id');
        $this->jsonList('companies', 'benefits', 'company_benefits', 'company_id', 'benefits', 'benefit_id');
        $this->jsonList('company_talent_pool', 'tags', 'company_talent_pool_tag', 'company_talent_pool_id', 'tags', 'tag_id');
        $this->jsonList('candidate_preferences', 'desired_titles', 'candidate_preference_job_titles', 'candidate_preference_id', 'job_titles', 'job_title_id');
        $this->moveScreeningOptions();
        $this->moveApplicationAnswers();
        $this->moveCompanyLocations();

        // --- columns that duplicated information stored elsewhere ---
        $this->moveCompanyDocPath();
        $this->moveJobTeamColumns();
        $this->linkInterviewsToRounds();

        $this->dropColumn('jobs', 'department');
        $this->dropColumn('company_employees', 'requested_department');
        foreach (['jobs', 'candidate_profiles', 'candidate_preferences', 'salary_records'] as $t) {
            $this->dropColumn($t, 'location');
        }
    }

    public function down(): void
    {
        throw new \RuntimeException('Normalization step 2 is not reversible. Restore the DB backup instead.');
    }

    // ------------------------------------------------------------------ new child tables
    private function createChildTables(): void
    {
        if (! Schema::hasTable('company_locations')) {
            Schema::create('company_locations', function (Blueprint $t) {
                $t->id();
                $t->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $t->foreignId('area_id')->constrained('areas')->restrictOnDelete();
                $t->boolean('is_headquarters')->default(false);
                $t->timestamps();
                $t->unique(['company_id', 'area_id']);
            });
        }

        foreach ([['job_benefits', 'job_id', 'jobs'], ['company_benefits', 'company_id', 'companies']] as [$tbl, $fk, $parent]) {
            if (Schema::hasTable($tbl)) {
                continue;
            }

            Schema::create($tbl, function (Blueprint $t) use ($fk, $parent) {
                $t->foreignId($fk)->constrained($parent)->cascadeOnDelete();
                $t->foreignId('benefit_id')->constrained('benefits')->cascadeOnDelete();
                $t->timestamps();
                $t->primary([$fk, 'benefit_id']);
            });
        }

        if (! Schema::hasTable('company_talent_pool_tag')) {
            Schema::create('company_talent_pool_tag', function (Blueprint $t) {
                $t->foreignId('company_talent_pool_id')->constrained('company_talent_pool')->cascadeOnDelete();
                $t->foreignId('tag_id')->constrained('tags')->cascadeOnDelete();
                $t->primary(['company_talent_pool_id', 'tag_id']);
            });
        }

        if (! Schema::hasTable('candidate_preference_job_titles')) {
            Schema::create('candidate_preference_job_titles', function (Blueprint $t) {
                $t->foreignId('candidate_preference_id')->constrained('candidate_preferences')->cascadeOnDelete();
                $t->foreignId('job_title_id')->constrained('job_titles')->cascadeOnDelete();
                $t->primary(['candidate_preference_id', 'job_title_id']);
            });
        }

        if (! Schema::hasTable('job_screening_question_options')) {
            Schema::create('job_screening_question_options', function (Blueprint $t) {
                $t->id();
                $t->foreignId('job_screening_question_id')->constrained('job_screening_questions')->cascadeOnDelete();
                $t->string('label');
                $t->string('value')->nullable();
                $t->unsignedSmallInteger('sort_order')->default(0);
                $t->timestamps();
            });
        }

        if (! Schema::hasTable('application_answers')) {
            Schema::create('application_answers', function (Blueprint $t) {
                $t->id();
                $t->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
                $t->foreignId('job_screening_question_id')->constrained('job_screening_questions')->cascadeOnDelete();
                $t->text('answer_text')->nullable();
                $t->foreignId('selected_option_id')->nullable()->constrained('job_screening_question_options')->nullOnDelete();
                $t->timestamps();
                $t->unique(['application_id', 'job_screening_question_id'], 'app_answers_app_question_unique');
            });
        }
    }

    // ------------------------------------------------------------------ helpers
    private function qi(string $id): string
    {
        return DB::getQueryGrammar()->wrap($id);
    }

    private function dropColumn(string $table, string $column): void
    {
        if (! Schema::hasColumn($table, $column)) {
            return;
        }
        foreach (Schema::getIndexes($table) as $index) {
            if (($index['primary'] ?? false) || ! in_array($column, $index['columns'] ?? [], true)) {
                continue;
            }

            $name = $index['name'];
            try {
                Schema::table($table, fn (Blueprint $t) => ($index['unique'] ?? false)
                    ? $t->dropUnique($name)
                    : $t->dropIndex($name));
            } catch (\Throwable) {
            }
        }
        Schema::table($table, fn (Blueprint $t) => $t->dropColumn($column));
    }

    /** Insert $value into a lookup if missing and return its id. */
    private function ensureLookup(string $lookup, string $value): int
    {
        $key = in_array($lookup, $this->nameKeyed, true) ? 'name' : 'code';
        $id = DB::table($lookup)->where($key, $value)->value('id');
        if ($id) {
            return (int) $id;
        }
        $row = [$key => $value, 'created_at' => now(), 'updated_at' => now()];
        if ($key === 'code') {
            $row['name'] = Str::headline($value);
        }
        return (int) DB::table($lookup)->insertGetId($row);
    }

    private function convert(string $table, string $old, string $lookup, string $new, bool $required, ?string $default): void
    {
        if (! Schema::hasColumn($table, $old)) {
            return;
        }
        if (! Schema::hasColumn($table, $new)) {
            Schema::table($table, function (Blueprint $t) use ($new, $lookup) {
                $t->foreignId($new)->nullable()->constrained($lookup)->restrictOnDelete();
            });
        }

        $key = in_array($lookup, $this->nameKeyed, true) ? 'name' : 'code';
        foreach (DB::table($table)->whereNotNull($old)->distinct()->pluck($old) as $value) {
            if ((string) $value !== '') {
                $this->ensureLookup($lookup, (string) $value);
            }
        }

        DB::statement(sprintf(
            'UPDATE %1$s SET %2$s = (SELECT l.id FROM %3$s l WHERE l.%4$s = %1$s.%5$s LIMIT 1) WHERE %5$s IS NOT NULL',
            $this->qi($table), $this->qi($new), $this->qi($lookup), $this->qi($key), $this->qi($old)
        ));

        if ($default !== null) {
            DB::table($table)->whereNull($new)->update([$new => $this->ensureLookup($lookup, $default)]);
        }

        if ($required) {
            Schema::table($table, fn (Blueprint $t) => $t->unsignedBigInteger($new)->nullable(false)->change());
        }

        $this->dropColumn($table, $old);
    }

    private function convertDepartments(string $table, string $old, string $new): void
    {
        if (! Schema::hasColumn($table, $old)) {
            return;
        }
        if (! Schema::hasColumn($table, $new)) {
            Schema::table($table, function (Blueprint $t) use ($new) {
                $t->foreignId($new)->nullable()->constrained('departments')->nullOnDelete();
            });
        }
        $rows = DB::table($table)->whereNotNull($old)->where($old, '!=', '')->select('company_id', $old)->distinct()->get();
        foreach ($rows as $r) {
            $name = trim((string) $r->{$old});
            $id = DB::table('departments')->where('company_id', $r->company_id)->where('name', $name)->value('id')
                ?? DB::table('departments')->insertGetId(['company_id' => $r->company_id, 'name' => $name, 'created_at' => now(), 'updated_at' => now()]);
            DB::table($table)->where('company_id', $r->company_id)->where($old, $r->{$old})->update([$new => $id]);
        }
    }

    private function convertCities(string $table, string $old, string $new): void
    {
        if (! Schema::hasColumn($table, $old)) {
            return;
        }
        if (! Schema::hasColumn($table, $new)) {
            Schema::table($table, function (Blueprint $t) use ($new) {
                $t->foreignId($new)->nullable()->constrained('cities')->nullOnDelete();
            });
        }
        foreach (DB::table($table)->whereNotNull($old)->where($old, '!=', '')->distinct()->pluck($old) as $value) {
            $name = trim((string) $value);
            $id = DB::table('cities')->where('name', $name)->value('id');
            if (! $id) {
                $parts = array_map('trim', explode(',', $name));
                $countryName = count($parts) > 1 ? array_pop($parts) : null;
                $cityName = implode(', ', $parts);
                $countryId = $countryName
                    ? DB::table('countries')->where('name', $countryName)->value('id')
                    : null;
                if ($countryId && $cityName !== '') {
                    $id = DB::table('cities')->where('country_id', $countryId)->where('name', $cityName)->value('id');
                    $id ??= DB::table('cities')->insertGetId([
                        'country_id' => $countryId, 'name' => $cityName,
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
            }
            if (! $id) {
                continue;
            }
            DB::table($table)->where($old, $value)->update([$new => $id]);
        }
    }

    /** Legacy JSON items can be "Foo" or {"name": "Foo"} / {"label": "Foo"}. */
    private function label(mixed $item): ?string
    {
        if (is_array($item)) {
            $item = $item['name'] ?? $item['label'] ?? $item['title'] ?? $item['city'] ?? null;
        }
        $item = is_string($item) ? trim($item) : null;
        return $item === '' ? null : $item;
    }

    private function decode(mixed $raw): array
    {
        $v = is_string($raw) ? json_decode($raw, true) : $raw;
        return is_array($v) ? $v : [];
    }

    private function jsonList(string $table, string $col, string $pivot, string $ownerFk, string $lookup, string $lookupFk): void
    {
        if (! Schema::hasColumn($table, $col)) {
            return;
        }
        foreach (DB::table($table)->whereNotNull($col)->select('id', $col)->get() as $row) {
            foreach ($this->decode($row->{$col}) as $item) {
                if (($name = $this->label($item)) === null) {
                    continue;
                }
                DB::table($pivot)->insertOrIgnore([$ownerFk => $row->id, $lookupFk => $this->ensureLookup($lookup, $name)]);
            }
        }
        $this->dropColumn($table, $col);
    }

    private function moveScreeningOptions(): void
    {
        if (! Schema::hasColumn('job_screening_questions', 'options')) {
            return;
        }
        foreach (DB::table('job_screening_questions')->whereNotNull('options')->select('id', 'options')->get() as $q) {
            foreach (array_values($this->decode($q->options)) as $i => $opt) {
                $label = $this->label($opt);
                if ($label === null) {
                    continue;
                }
                DB::table('job_screening_question_options')->insert([
                    'job_screening_question_id' => $q->id, 'label' => $label,
                    'value' => is_array($opt) ? ($opt['value'] ?? null) : null,
                    'sort_order' => $i, 'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }
        $this->dropColumn('job_screening_questions', 'options');
    }

    /** answers JSON is either {question_id: answer} or [{question_id, answer}] */
    private function moveApplicationAnswers(): void
    {
        if (! Schema::hasColumn('applications', 'answers')) {
            return;
        }
        foreach (DB::table('applications')->whereNotNull('answers')->select('id', 'answers')->get() as $a) {
            foreach ($this->decode($a->answers) as $k => $v) {
                $qid = is_array($v) ? ($v['question_id'] ?? null) : (is_numeric($k) ? $k : null);
                $text = is_array($v) ? ($v['answer'] ?? $v['value'] ?? null) : $v;
                if (! $qid || ! DB::table('job_screening_questions')->where('id', $qid)->exists()) {
                    continue;
                }
                DB::table('application_answers')->insertOrIgnore([
                    'application_id' => $a->id, 'job_screening_question_id' => $qid,
                    'answer_text' => is_scalar($text) ? (string) $text : json_encode($text),
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }
        $this->dropColumn('applications', 'answers');
    }

    private function moveCompanyLocations(): void
    {
        $hasHq = Schema::hasColumn('companies', 'headquarters');
        $hasLocations = Schema::hasColumn('companies', 'locations');
        if (! $hasHq && ! $hasLocations) {
            return;
        }

        $columns = ['id'];
        if ($hasHq) $columns[] = 'headquarters';
        if ($hasLocations) $columns[] = 'locations';
        foreach (DB::table('companies')->select($columns)->get() as $c) {
            $hq = $hasHq && $c->headquarters ? trim($c->headquarters) : null;
            $names = $hasLocations
                ? array_filter(array_map(fn ($i) => $this->label($i), $this->decode($c->locations)))
                : [];
            if ($hq) {
                $names[] = $hq;
            }
            foreach (array_unique($names) as $name) {
                $areaId = DB::table('areas')->where('name', $name)->value('id');
                if ($areaId) {
                    DB::table('company_locations')->updateOrInsert(
                        ['company_id' => $c->id, 'area_id' => $areaId],
                        ['is_headquarters' => $name === $hq, 'created_at' => now(), 'updated_at' => now()]
                    );
                }
            }
        }
        if ($hasLocations) $this->dropColumn('companies', 'locations');
        if ($hasHq) $this->dropColumn('companies', 'headquarters');
    }

    /** companies.doc_path duplicated company_documents */
    private function moveCompanyDocPath(): void
    {
        if (! Schema::hasColumn('companies', 'doc_path')) {
            return;
        }
        $typeId = $this->ensureLookup('document_types', 'commercial_register');
        foreach (DB::table('companies')->whereNotNull('doc_path')->select('id', 'doc_path')->get() as $c) {
            $exists = DB::table('company_documents')->where('company_id', $c->id)->where('storage_path', $c->doc_path)->exists();
            if (! $exists) {
                DB::table('company_documents')->insert([
                    'company_id' => $c->id, 'document_type_id' => $typeId,
                    'original_filename' => basename($c->doc_path), 'storage_path' => $c->doc_path,
                    'size_bytes' => 0, 'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }
        $this->dropColumn('companies', 'doc_path');
    }

    /** jobs.requisition_owner_id / hiring_manager_id duplicated job_hiring_team */
    private function moveJobTeamColumns(): void
    {
        foreach (['requisition_owner_id' => 'requisition_owner', 'hiring_manager_id' => 'hiring_manager'] as $col => $role) {
            if (! Schema::hasColumn('jobs', $col)) {
                continue;
            }
            $roleId = $this->ensureLookup('hiring_team_roles', $role);
            foreach (DB::table('jobs')->whereNotNull($col)->select('id', $col)->get() as $j) {
                DB::table('job_hiring_team')->updateOrInsert(
                    ['job_id' => $j->id, 'user_id' => $j->{$col}],
                    ['hiring_team_role_id' => $roleId, 'created_at' => now(), 'updated_at' => now()]
                );
            }
            try {
                Schema::table('jobs', fn (Blueprint $t) => $t->dropForeign([$col]));
            } catch (\Throwable) {
            }
            $this->dropColumn('jobs', $col);
        }
    }

    /** interviews.round_no was a loose copy of job_interview_rounds.round_no -> real FK */
    private function linkInterviewsToRounds(): void
    {
        if (! Schema::hasColumn('interviews', 'round_no')) {
            return;
        }
        Schema::table('interviews', function (Blueprint $t) {
            $t->foreignId('job_interview_round_id')->nullable()->constrained('job_interview_rounds')->nullOnDelete();
        });
        DB::statement('UPDATE interviews SET job_interview_round_id = (
            SELECT r.id FROM job_interview_rounds r
            JOIN applications a ON a.job_id = r.job_id
            WHERE a.id = interviews.application_id AND r.round_no = interviews.round_no LIMIT 1)');
        $this->dropColumn('interviews', 'round_no');
    }
};
