<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Normalization step 4 - ONE `statuses` table for every status in the system.
 *
 *  - the old active/deactive `statuses` table is replaced by a unified one: (type, code, name, sort_order)
 *  - the ~21 per-entity status tables (job_statuses, application_statuses, account_statuses, ...) are copied
 *    into it (nothing is lost) and dropped
 *  - every business table gets a plain `status_id` FK to it; no enum / string status column is left
 *      offers                          status_id + approval_status_id
 *      application_status_history      from_status_id + to_status_id
 *  - the old active/deactive `status_id` (9 tables) is merged into the lifecycle status, see $recordTables
 *
 * Not reversible: take a DB backup first.
 */
return new class extends Migration
{
    /** [table, old FK column, old lookup table, new FK column, NOT NULL?, default code] */
    private array $moves = [
        ['users', 'account_status_id', 'account_statuses', 'status_id', true, 'active'],
        ['admins', 'account_status_id', 'account_statuses', 'status_id', true, 'active'],
        ['companies', 'company_approval_status_id', 'company_approval_statuses', 'status_id', true, 'pending'],
        ['company_employees', 'company_employee_status_id', 'company_employee_statuses', 'status_id', true, 'pending'],
        ['company_invitations', 'invitation_status_id', 'invitation_statuses', 'status_id', true, 'pending'],
        ['jobs', 'job_status_id', 'job_statuses', 'status_id', true, 'draft'],
        ['offers', 'offer_status_id', 'offer_statuses', 'status_id', true, 'sent'],
        ['offers', 'offer_approval_status_id', 'offer_approval_statuses', 'approval_status_id', true, 'not_required'],
        ['applications', 'application_status_id', 'application_statuses', 'status_id', true, 'submitted'],
        ['application_status_history', 'from_application_status_id', 'application_statuses', 'from_status_id', false, null],
        ['application_status_history', 'to_application_status_id', 'application_statuses', 'to_status_id', true, null],
        ['interviews', 'interview_status_id', 'interview_statuses', 'status_id', true, 'pending'],
        ['interviewer_availability', 'availability_status_id', 'availability_statuses', 'status_id', true, 'open'],
        ['interview_bookings', 'booking_status_id', 'booking_statuses', 'status_id', true, 'booked'],
        ['exams', 'exam_status_id', 'exam_statuses', 'status_id', true, 'draft'],
        ['questions', 'question_status_id', 'question_statuses', 'status_id', true, 'draft'],
        ['exam_attempts', 'attempt_status_id', 'attempt_statuses', 'status_id', true, 'in_progress'],
        ['grading_results', 'grading_status_id', 'grading_statuses', 'status_id', true, 'pending'],
        ['cv_match_results', 'cv_run_status_id', 'cv_run_statuses', 'status_id', true, 'pending'],
        ['calendar_integrations', 'sync_status_id', 'sync_statuses', 'status_id', false, null],
        ['notifications', 'delivery_status_id', 'delivery_statuses', 'status_id', true, 'pending'],
        ['contact_messages', 'contact_message_status_id', 'contact_message_statuses', 'status_id', true, 'new'],
        ['agency_engagements', 'engagement_status_id', 'engagement_statuses', 'status_id', true, 'draft'],
        ['agency_engagement_submissions', 'submission_status_id', 'submission_statuses', 'status_id', true, 'pending_review'],
    ];

    /**
     * Tables that carried the old two-value `status_id` (1 = active, 2 = deactive).
     * [status type of the table, status a "deactive" row becomes]. EDIT THIS MAP if you want other targets.
     */
    private array $recordTables = [
        'companies' => ['company_approval', 'suspended'],
        'candidate_profiles' => ['candidate_profile', 'inactive'],
        'candidate_education' => ['candidate_education', 'inactive'],
        'countries' => ['account', 'deactivated'],
        'jobs' => ['job', 'archived'],
        'applications' => ['application', 'withdrawn'],
        'offers' => ['offer', 'expired'],
        'interviews' => ['interview', 'cancelled'],
        'exams' => ['exam', 'archived'],
        'contact_messages' => ['contact_message', 'closed'],
        'agency_engagements' => ['engagement', 'cancelled'],
    ];

    /** @var array<string,int> */
    private array $idCache = [];

    public function up(): void
    {
        // A previous attempt may have rebuilt the shared lookup and then stopped
        // before this migration was recorded. Resume by restoring the preserved ids.
        if (Schema::hasColumn('statuses', 'type') && Schema::hasColumn('statuses', 'code')) {
            $this->recoverPartiallyUnifiedSchema();

            return;
        }

        $this->attempt(fn () => Schema::table('jobs', fn (Blueprint $t) => $t->dropIndex('jobs_listing_idx')));

        // 1. park the old active/deactive column so the old `statuses` table can be replaced
        foreach (array_keys($this->recordTables) as $table) {
            if (Schema::hasColumn($table, 'status_id') && ! Schema::hasColumn($table, 'legacy_record_status_id')) {
                $this->attempt(fn () => Schema::table($table, fn (Blueprint $t) => $t->dropForeign(['status_id'])));
                $this->attempt(fn () => Schema::table($table, fn (Blueprint $t) => $t->dropIndex($table . '_status_id_index')));
                Schema::table($table, fn (Blueprint $t) => $t->renameColumn('status_id', 'legacy_record_status_id'));
            }
        }

        // 2. the unified table
        Schema::dropIfExists('statuses');
        Schema::create('statuses', function (Blueprint $t) {
            $t->id();
            $t->string('type', 50);   // entity the status belongs to: job, application, account, ...
            $t->string('code', 100);  // stable machine value: draft, published, ...
            $t->string('name');
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->timestamps();
            $t->unique(['type', 'code']);
            $t->index('type');
        });
        $this->seedStatuses();

        // 3. every FK -> statuses.id
        foreach ($this->moves as [$table, $old, $oldLookup, $new, $required, $default]) {
            $this->move($table, $old, $oldLookup, $new, (bool) $required, $default);
        }
        $this->convertCandidateProfiles();
        $this->convertCandidateEducation();
        $this->convertCountries();
        $this->convertCompanyStatus();

        foreach ([
            'jobs' => ['job', 'draft'],
            'applications' => ['application', 'submitted'],
            'offers' => ['offer', 'sent'],
            'interviews' => ['interview', 'pending'],
            'exams' => ['exam', 'draft'],
            'contact_messages' => ['contact_message', 'new'],
            'agency_engagements' => ['engagement', 'draft'],
        ] as $table => [$type, $defaultCode]) {
            $this->ensureRecordStatus($table, $type, $defaultCode);
        }

        // 4. old active/deactive flag -> lifecycle status
        foreach ($this->recordTables as $table => [$type, $deactiveAs]) {
            if (in_array($table, ['candidate_education', 'countries'], true) && Schema::hasColumn($table, 'status_id')) {
                DB::table($table)->update(['status_id' => $this->idOf($type, 'active')]);
            }
            if ($table !== 'candidate_profiles' && Schema::hasColumn($table, 'legacy_record_status_id')) {
                // legacy ids: 1 = active, 2 = deactive
                DB::table($table)->where('legacy_record_status_id', 2)
                    ->update(['status_id' => $this->idOf($type, $deactiveAs)]);
            }
            $this->dropFkColumn($table, 'legacy_record_status_id');
        }

        // 5. old per-entity status tables are now redundant
        foreach (array_unique(array_column($this->moves, 2)) as $oldLookup) {
            Schema::dropIfExists($oldLookup);
        }

        $this->attempt(fn () => Schema::table('jobs', fn (Blueprint $t) => $t->index(['status_id', 'visibility_id', 'deadline'], 'jobs_listing_idx')));
        $this->attempt(fn () => Schema::table('agency_engagements', fn (Blueprint $t) => $t->index(['agency_company_id', 'status_id'])));
        $this->attempt(fn () => Schema::table('agency_engagements', fn (Blueprint $t) => $t->index(['client_company_id', 'status_id'])));
        $this->attempt(fn () => Schema::table('agency_engagement_submissions', fn (Blueprint $t) => $t->index(['engagement_id', 'status_id'])));
    }

    public function down(): void
    {
        throw new \RuntimeException('Normalization step 4 is not reversible. Restore the DB backup instead.');
    }

    // ------------------------------------------------------------------ helpers

    private function attempt(\Closure $fn): void
    {
        try {
            $fn();
        } catch (\Throwable) {
        }
    }

    private function recoverPartiallyUnifiedSchema(): void
    {
        foreach (array_keys($this->recordTables) as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'legacy_record_status_id')) {
                continue;
            }

            if (Schema::hasColumn($table, 'status_id')) {
                $this->dropFkColumn($table, 'legacy_record_status_id');
                continue;
            }

            $this->attempt(fn () => Schema::table($table, fn (Blueprint $t) => $t->dropIndex($table . '_legacy_record_status_id_index')));
            Schema::table($table, fn (Blueprint $t) => $t->renameColumn('legacy_record_status_id', 'status_id'));
            Schema::table($table, function (Blueprint $t) {
                $t->foreign('status_id')->references('id')->on('statuses')->restrictOnDelete();
                $t->index('status_id');
            });
        }

        foreach (array_unique(array_column($this->moves, 2)) as $oldLookup) {
            Schema::dropIfExists($oldLookup);
        }

        $this->attempt(fn () => Schema::table('jobs', fn (Blueprint $t) => $t->index(['status_id', 'visibility_id', 'deadline'], 'jobs_listing_idx')));
        $this->attempt(fn () => Schema::table('agency_engagements', fn (Blueprint $t) => $t->index(['agency_company_id', 'status_id'])));
        $this->attempt(fn () => Schema::table('agency_engagements', fn (Blueprint $t) => $t->index(['client_company_id', 'status_id'])));
        $this->attempt(fn () => Schema::table('agency_engagement_submissions', fn (Blueprint $t) => $t->index(['engagement_id', 'status_id'])));
    }

    private function dropFkColumn(string $table, string $column): void
    {
        if (! Schema::hasColumn($table, $column)) {
            return;
        }
        $this->attempt(fn () => Schema::table($table, fn (Blueprint $t) => $t->dropForeign([$column])));
        $this->attempt(fn () => Schema::table($table, fn (Blueprint $t) => $t->dropIndex([$column])));
        Schema::table($table, fn (Blueprint $t) => $t->dropColumn($column));
    }

    /** 'job_statuses' => 'job', 'account_statuses' => 'account' */
    private function typeOf(string $oldLookup): string
    {
        return preg_replace('/_statuses$/', '', $oldLookup);
    }

    private function idOf(string $type, string $code): int
    {
        $key = $type . ':' . $code;
        if (! isset($this->idCache[$key])) {
            $id = DB::table('statuses')->where('type', $type)->where('code', $code)->value('id');
            if (! $id) {
                throw new \RuntimeException("Status \"{$code}\" of type \"{$type}\" does not exist.");
            }
            $this->idCache[$key] = (int) $id;
        }

        return $this->idCache[$key];
    }

    private function seedStatuses(): void
    {
        $now = now();

        // account statuses first, with their ORIGINAL ids (1 active, 2 deactivated, 3 blocked, 4 deleted)
        if (Schema::hasTable('account_statuses')) {
            foreach (DB::table('account_statuses')->orderBy('id')->get() as $r) {
                DB::table('statuses')->insert([
                    'id' => $r->id, 'type' => 'account', 'code' => Str::snake($r->name), 'name' => $r->name,
                    'sort_order' => $r->id, 'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }

        // every other status lookup is copied as-is (including values added by earlier migrations)
        foreach (array_unique(array_column($this->moves, 2)) as $oldLookup) {
            if ($oldLookup === 'account_statuses' || ! Schema::hasTable($oldLookup)) {
                continue;
            }
            $type = $this->typeOf($oldLookup);
            foreach (DB::table($oldLookup)->orderBy('sort_order')->orderBy('id')->get() as $r) {
                DB::table('statuses')->insert([
                    'type' => $type, 'code' => $r->code, 'name' => $r->name,
                    'sort_order' => $r->sort_order, 'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }

        // candidate profiles only had the old active/deactive flag
        foreach ([['active', 'Active', 1], ['inactive', 'Inactive', 2]] as [$code, $name, $order]) {
            DB::table('statuses')->insert([
                'type' => 'candidate_profile', 'code' => $code, 'name' => $name,
                'sort_order' => $order, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        foreach ([['active', 'Active', 1], ['inactive', 'Inactive', 2]] as [$code, $name, $order]) {
            DB::table('statuses')->insert([
                'type' => 'candidate_education', 'code' => $code, 'name' => $name,
                'sort_order' => $order, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }

    }

    private function move(string $table, string $old, string $oldLookup, string $new, bool $required, ?string $default): void
    {
        if (! Schema::hasColumn($table, $old) || ! Schema::hasTable($oldLookup)) {
            return;
        }
        $type = $this->typeOf($oldLookup);
        $byName = $oldLookup === 'account_statuses';

        if (! Schema::hasColumn($table, $new)) {
            Schema::table($table, function (Blueprint $t) use ($new) {
                $t->foreignId($new)->nullable()->constrained('statuses')->restrictOnDelete();
            });
        }

        foreach (DB::table($oldLookup)->get() as $r) {
            $code = $byName ? Str::snake($r->name) : $r->code;
            DB::table($table)->where($old, $r->id)->update([$new => $this->idOf($type, $code)]);
        }

        if ($default !== null) {
            DB::table($table)->whereNull($new)->update([$new => $this->idOf($type, $default)]);
        }

        if ($required) {
            $defaultId = $type === 'account' && $default === 'active'
                ? 1
                : ($default !== null ? $this->idOf($type, $default) : null);
            $this->makeRequired($table, $new, $defaultId);
        }

        $this->dropFkColumn($table, $old);
    }

    private function convertCandidateProfiles(): void
    {
        if (! Schema::hasColumn('candidate_profiles', 'status_id')) {
            Schema::table('candidate_profiles', function (Blueprint $t) {
                $t->foreignId('status_id')->nullable()->constrained('statuses')->restrictOnDelete();
            });
        }
        DB::table('candidate_profiles')->update(['status_id' => $this->idOf('candidate_profile', 'active')]);
        if (Schema::hasColumn('candidate_profiles', 'legacy_record_status_id')) {
            DB::table('candidate_profiles')->where('legacy_record_status_id', 2)
                ->update(['status_id' => $this->idOf('candidate_profile', 'inactive')]);
        }
        $this->makeRequired('candidate_profiles', 'status_id', $this->idOf('candidate_profile', 'active'));
    }

    private function convertCandidateEducation(): void
    {
        if (! Schema::hasTable('candidate_education')) {
            return;
        }

        if (! Schema::hasColumn('candidate_education', 'status_id')) {
            Schema::table('candidate_education', function (Blueprint $t) {
                $t->foreignId('status_id')->nullable()->constrained('statuses')->restrictOnDelete();
            });
        }

        DB::table('candidate_education')->update([
            'status_id' => $this->idOf('candidate_education', 'active'),
        ]);
        $this->makeRequired('candidate_education', 'status_id', $this->idOf('candidate_education', 'active'));
    }

    private function convertCountries(): void
    {
        if (! Schema::hasTable('countries')) {
            return;
        }

        if (! Schema::hasColumn('countries', 'status_id')) {
            Schema::table('countries', function (Blueprint $t): void {
                $t->foreignId('status_id')->nullable()->constrained('statuses')->restrictOnDelete();
            });
        }

        DB::table('countries')->update(['status_id' => $this->idOf('account', 'active')]);
        $this->makeRequired('countries', 'status_id', $this->idOf('account', 'active'));
    }

    private function convertCompanyStatus(): void
    {
        if (! Schema::hasTable('companies') || ! Schema::hasColumn('companies', 'company_status_id')) {
            return;
        }

        if (! Schema::hasColumn('companies', 'status_id')) {
            Schema::table('companies', function (Blueprint $t): void {
                $t->foreignId('status_id')->nullable()->constrained('statuses')->restrictOnDelete();
            });
        }

        foreach (DB::table('companies')->select('id', 'company_status_id')->get() as $company) {
            $name = DB::table('company_statuses')->where('id', $company->company_status_id)->value('name') ?? 'pending';
            DB::table('companies')->where('id', $company->id)->update([
                'status_id' => $this->idOf('company_approval', Str::snake($name)),
            ]);
        }
        $this->makeRequired('companies', 'status_id', $this->idOf('company_approval', 'pending'));
    }

    private function ensureRecordStatus(string $table, string $type, string $defaultCode): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        if (! Schema::hasColumn($table, 'status_id')) {
            Schema::table($table, function (Blueprint $t): void {
                $t->foreignId('status_id')->nullable()->constrained('statuses')->restrictOnDelete();
            });
            DB::table($table)->update(['status_id' => $this->idOf($type, $defaultCode)]);
        }

        $this->makeRequired($table, 'status_id', $this->idOf($type, $defaultCode));
    }

    /** Keep the default pointed at the correct status record for this entity. */
    private function makeRequired(string $table, string $column, ?int $defaultId): void
    {
        Schema::table($table, function (Blueprint $t) use ($column, $defaultId) {
            $col = $t->unsignedBigInteger($column)->nullable(false);
            if ($defaultId !== null) {
                $col->default($defaultId);
            }
            $col->change();
        });
    }
};
