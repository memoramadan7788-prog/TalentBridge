<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Approval is its own concept: `approval_statuses` (under_review / accepted / rejected) and every table that
 * needs an approval points to it through `approval_status_id`:  companies, company_employees, candidate_profiles.
 *
 * The approval codes that used to be mixed into `statuses` (pending / approved / rejected) are removed from there,
 * so nothing is stored twice. What stays in `statuses`:
 *      company           : active, suspended
 *      company_employee  : active, removed
 *
 * Existing rows:
 *      pending -> under_review      approved -> accepted      rejected -> rejected
 *      suspended companies and removed employees were approved before -> accepted (+ status suspended / removed)
 *      candidate profiles had no approval before -> all existing profiles become accepted
 *
 * Not reversible: take a DB backup first.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_statuses', function (Blueprint $t) {
            $t->id();
            $t->string('code', 100)->unique();
            $t->string('name');
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->timestamps();
        });
        foreach ([['under_review', 'Under Review', 1], ['accepted', 'Accepted', 2], ['rejected', 'Rejected', 3]] as [$code, $name, $order]) {
            DB::table('approval_statuses')->insert([
                'code' => $code, 'name' => $name, 'sort_order' => $order,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        $this->splitApproval('companies', 'company_approval', 'company', [
            'pending' => 'under_review', 'approved' => 'accepted', 'rejected' => 'rejected', 'suspended' => 'accepted',
        ]);
        $this->splitApproval('company_employees', 'company_employee', 'company_employee', [
            'pending' => 'under_review', 'approved' => 'accepted', 'rejected' => 'rejected', 'removed' => 'accepted',
        ]);

        // The approval split removes pending/rejected lifecycle rows. Point DB defaults
        // at the surviving active rows so inserts that omit status_id remain valid.
        $this->setStatusDefault('companies', 'company', 'active');
        $this->setStatusDefault('company_employees', 'company_employee', 'active');

        $this->addApprovalColumn('candidate_profiles');
        DB::table('candidate_profiles')->update(['approval_status_id' => $this->approvalId('accepted')]);
        $this->makeRequired('candidate_profiles');
        $this->setStatusDefault('candidate_profiles', 'candidate_profile', 'active');
    }

    public function down(): void
    {
        throw new \RuntimeException('This migration is not reversible. Restore the DB backup instead.');
    }

    private function approvalId(string $code): int
    {
        return (int) DB::table('approval_statuses')->where('code', $code)->value('id');
    }

    private function addApprovalColumn(string $table): void
    {
        Schema::table($table, function (Blueprint $t) {
            $t->foreignId('approval_status_id')->nullable()->constrained('approval_statuses')->restrictOnDelete();
        });
    }

    private function makeRequired(string $table): void
    {
        Schema::table($table, fn (Blueprint $t) => $t->unsignedBigInteger('approval_status_id')->nullable(false)->change());
    }

    private function setStatusDefault(string $table, string $type, string $code): void
    {
        if (! Schema::hasColumn($table, 'status_id')) {
            return;
        }

        $id = DB::table('statuses')->where('type', $type)->where('code', $code)->value('id');
        if ($id) {
            Schema::table($table, fn (Blueprint $t) => $t->unsignedBigInteger('status_id')->default($id)->nullable(false)->change());
        }
    }

    /** @param  array<string,string>  $map  old status code => approval code */
    private function splitApproval(string $table, string $oldType, string $newType, array $map): void
    {
        $this->addApprovalColumn($table);

        // 1. approval from the old status
        foreach ($map as $oldCode => $approvalCode) {
            $sid = DB::table('statuses')->where('type', $oldType)->where('code', $oldCode)->value('id');
            if ($sid) {
                DB::table($table)->where('status_id', $sid)->update(['approval_status_id' => $this->approvalId($approvalCode)]);
            }
        }
        DB::table($table)->whereNull('approval_status_id')->update(['approval_status_id' => $this->approvalId('under_review')]);
        $this->makeRequired($table);

        // 2. `approved` row becomes the plain `active` status; pending / rejected rows are folded into it and removed
        $activeId = DB::table('statuses')->where('type', $oldType)->where('code', 'approved')->value('id');
        if ($activeId) {
            DB::table('statuses')->where('id', $activeId)
                ->update(['type' => $newType, 'code' => 'active', 'name' => 'Active', 'sort_order' => 1]);
            foreach (['pending', 'rejected'] as $code) {
                $id = DB::table('statuses')->where('type', $oldType)->where('code', $code)->value('id');
                if ($id) {
                    DB::table($table)->where('status_id', $id)->update(['status_id' => $activeId]);
                    DB::table('statuses')->where('id', $id)->delete();
                }
            }
        }

        // 3. what is left (suspended / removed) moves to the new type
        DB::table('statuses')->where('type', $oldType)->where('code', '!=', 'active')
            ->update(['type' => $newType, 'sort_order' => 2]);
    }
};
