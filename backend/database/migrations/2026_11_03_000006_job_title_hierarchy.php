<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Category -> Subcategory -> Job title
 *
 *   job_categories (parent_id = NULL)        Software
 *   job_categories (parent_id = Software)    Backend          <- subcategory
 *   job_titles     (job_category_id = Backend)  Node.js Backend Developer
 *   jobs           (job_title_id)            a posting for that title
 *
 * job_categories already had parent_id. New here: job_titles.job_category_id and jobs.job_title_id.
 * Legacy job titles (used by candidate experience, salary records, ...) stay valid with no subcategory.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_titles', function (Blueprint $t) {
            $t->foreignId('job_category_id')->nullable()->constrained('job_categories')->nullOnDelete();
        });

        Schema::table('jobs', function (Blueprint $t) {
            $t->foreignId('job_title_id')->nullable()->after('category_id')->constrained('job_titles')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $t) {
            $t->dropConstrainedForeignId('job_title_id');
        });
        Schema::table('job_titles', function (Blueprint $t) {
            $t->dropConstrainedForeignId('job_category_id');
        });
    }
};
