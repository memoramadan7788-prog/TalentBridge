<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * CV scan (no job involved): when a CV is uploaded the AI reads it and returns
 * a score, strengths, weaknesses and suggested job titles.
 *
 *   candidate_cvs 1 ─ 1 cv_scans            score + summary + run status
 *   cv_scans      1 ─ n cv_scan_points      strengths / weaknesses (one row each)
 *   candidate_cvs n ─ n job_titles          matched_jobs (the titles the AI suggested)
 *
 * The existing cv_match_results table is NOT touched: it still holds the
 * "application vs. job posting" evaluation used by the ATS.
 */
return new class extends Migration
{
    public function up(): void
    {
        // strength / weakness lookup (same pattern as the other lookup tables)
        Schema::create('cv_scan_point_types', function (Blueprint $t) {
            $t->id();
            $t->string('code', 100)->unique();
            $t->string('name');
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->timestamps();
        });
        foreach ([['strength', 'Strength', 1], ['weakness', 'Weakness', 2]] as [$code, $name, $order]) {
            DB::table('cv_scan_point_types')->insert([
                'code' => $code, 'name' => $name, 'sort_order' => $order,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        // one scan per CV (a re-scan updates the same row)
        Schema::create('cv_scans', function (Blueprint $t) {
            $t->id();
            $t->foreignId('cv_id')->unique()->constrained('candidate_cvs')->cascadeOnDelete();
            // statuses.type = 'cv_run' : pending / ok / failed / timeout
            $t->foreignId('status_id')->constrained('statuses')->restrictOnDelete();
            $t->decimal('score', 5, 2)->nullable();          // null until the scan has finished
            $t->text('summary')->nullable();
            $t->string('model_version')->nullable();
            $t->string('request_id')->nullable();
            $t->text('error_message')->nullable();
            $t->timestamp('finished_at')->nullable();
            $t->foreignId('UID')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('LUID')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('cv_scan_points', function (Blueprint $t) {
            $t->id();
            $t->foreignId('cv_scan_id')->constrained('cv_scans')->cascadeOnDelete();
            $t->foreignId('cv_scan_point_type_id')->constrained('cv_scan_point_types')->restrictOnDelete();
            $t->text('content');
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->timestamps();
            $t->index(['cv_scan_id', 'cv_scan_point_type_id'], 'cv_scan_points_scan_type_idx');
        });

        // job titles the AI suggested for a CV
        Schema::create('matched_jobs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('cv_id')->constrained('candidate_cvs')->cascadeOnDelete();
            $t->foreignId('job_title_id')->constrained('job_titles')->restrictOnDelete();
            $t->decimal('confidence', 5, 2)->nullable();      // optional, if the model returns one
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->timestamps();
            $t->unique(['cv_id', 'job_title_id'], 'matched_jobs_cv_title_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matched_jobs');
        Schema::dropIfExists('cv_scan_points');
        Schema::dropIfExists('cv_scans');
        Schema::dropIfExists('cv_scan_point_types');
    }
};
