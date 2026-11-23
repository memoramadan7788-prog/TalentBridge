<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Normalization step 3/3 - remove duplicated columns and fix integrity problems.
 *
 *  1. roles.scope / admin_roles.scope duplicated role_categories -> dropped
 *  2. user_roles UNIQUE(user, role, company) did not stop duplicates for platform roles (company_id NULL)
 *  3. company_employees.role_id duplicated user_roles -> dropped
 *  4. companies had 3 status columns (status, account_status_id, status_id) and a user reviewer next to the admin reviewer
 *  5. candidate_profiles.visibility_id remains the public/private record visibility FK
 *  6. company_workspaces copied size/timezone/headquarters/description from companies
 *  7. jobs.visibility (enum) duplicated jobs.visibility_id
 *  8. interview_bookings copied interviewer/start/end from the availability slot, and its UNIQUE rules
 *     were dropped and left to the application -> replaced by one DB-enforced rule
 */
return new class extends Migration
{
    private function attempt(\Closure $fn): void
    {
        try {
            $fn();
        } catch (\Throwable) {
        }
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

    public function up(): void
    {
        // 1. roles.scope
        if (Schema::hasColumn('roles', 'scope')) {
            $company = DB::table('role_categories')->where('name', 'company')->value('id');
            $user = DB::table('role_categories')->where('name', 'user')->value('id');
            DB::table('roles')->whereNull('role_category_id')->where('scope', 'company')->update(['role_category_id' => $company]);
            DB::table('roles')->whereNull('role_category_id')->update(['role_category_id' => $user]);
            // the old FK was ON DELETE SET NULL, which is impossible on a NOT NULL column -> RESTRICT
            $this->attempt(fn () => Schema::table('roles', fn (Blueprint $t) => $t->dropForeign(['role_category_id'])));
            Schema::table('roles', fn (Blueprint $t) => $t->unsignedBigInteger('role_category_id')->nullable(false)->change());
            Schema::table('roles', fn (Blueprint $t) => $t->foreign('role_category_id')->references('id')->on('role_categories')->restrictOnDelete());
            Schema::table('roles', fn (Blueprint $t) => $t->dropColumn('scope'));
        }
        if (Schema::hasColumn('admin_roles', 'scope')) {
            Schema::table('admin_roles', fn (Blueprint $t) => $t->dropColumn('scope'));
        }

        // 2. user_roles uniqueness with NULL company
        $this->attempt(fn () => Schema::table('user_roles', fn (Blueprint $t) => $t->dropUnique('user_roles_user_id_role_id_company_id_unique')));
        if (! Schema::hasColumn('user_roles', 'company_key')) {
            Schema::table('user_roles', function (Blueprint $t) {
                // VIRTUAL (not STORED): MySQL forbids STORED generated columns on a column with ON DELETE SET NULL
                $t->unsignedBigInteger('company_key')->virtualAs('COALESCE(company_id, 0)');
                $t->unique(['user_id', 'role_id', 'company_key'], 'user_roles_user_role_company_unique');
            });
        }

        // 3. company_employees.role_id
        $this->dropFkColumn('company_employees', 'role_id');

        // 4. companies
        $this->dropFkColumn('companies', 'reviewed_by');
        $this->dropFkColumn('companies', 'account_status_id');

        // 6. company_workspaces duplicates
        foreach (['size', 'timezone', 'headquarters', 'description'] as $col) {
            if (Schema::hasColumn('company_workspaces', $col)) {
                Schema::table('company_workspaces', fn (Blueprint $t) => $t->dropColumn($col));
            }
        }

        // 7. jobs.visibility enum
        if (Schema::hasColumn('jobs', 'visibility')) {
            $this->attempt(fn () => Schema::table('jobs', fn (Blueprint $t) => $t->dropIndex('jobs_visibility_index')));
            Schema::table('jobs', fn (Blueprint $t) => $t->dropColumn('visibility'));
        }

        // 8. interview_bookings
        if (! Schema::hasColumn('interview_bookings', 'active_availability_id')) {
            Schema::table('interview_bookings', function (Blueprint $t) {
                // = availability_id while the booking is active, NULL once cancelled/deleted.
                // UNIQUE on it => one active booking per slot, and a cancelled slot can be re-booked.
                $t->unsignedBigInteger('active_availability_id')->nullable()->unique();
            });
            DB::statement("UPDATE interview_bookings SET active_availability_id = availability_id
                WHERE deleted_at IS NULL
                  AND booking_status_id NOT IN (SELECT id FROM booking_statuses WHERE code = 'cancelled')
                  AND id IN (SELECT MIN(id) FROM interview_bookings GROUP BY availability_id)");
        }
        foreach (['interviewer_id', 'start_at', 'end_at'] as $col) {
            $this->dropFkColumn('interview_bookings', $col);
        }
        $this->attempt(fn () => Schema::table('interview_bookings', fn (Blueprint $t) => $t->index('availability_id')));

        // composite index for the public job listing filter
        $this->attempt(fn () => Schema::table('jobs', fn (Blueprint $t) => $t->index(['job_status_id', 'visibility_id', 'deadline'], 'jobs_listing_idx')));
    }

    public function down(): void
    {
        throw new \RuntimeException('Normalization step 3 is not reversible. Restore the DB backup instead.');
    }
};
