<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * company_social_links + candidate_social_links  ->  ONE general `social_links` table.
 *
 *   social_platforms  (the shared platform list)  gets an `icon` column
 *   social_links      linkable_type/linkable_id (Company or CandidateProfile)
 *                     + social_platform_id -> social_platforms + url
 *
 * Existing links are copied (ids are new, UID/LUID/timestamps/deleted_at are kept),
 * then the two old tables are dropped.
 *
 * Not reversible: take a DB backup first.
 */
return new class extends Migration
{
    /** code => icon (Font Awesome classes - change to a path/URL if your frontend uses images) */
    private array $icons = [
        'linkedin'  => 'fa-brands fa-linkedin',
        'github'    => 'fa-brands fa-github',
        'twitter'   => 'fa-brands fa-x-twitter',
        'facebook'  => 'fa-brands fa-facebook',
        'instagram' => 'fa-brands fa-instagram',
        'behance'   => 'fa-brands fa-behance',
        'website'   => 'fa-solid fa-globe',
        'other'     => 'fa-solid fa-link',
    ];

    public function up(): void
    {
        // 1. icon on the platform list
        Schema::table('social_platforms', function (Blueprint $t) {
            $t->string('icon', 100)->nullable()->after('name');
        });
        foreach ($this->icons as $code => $icon) {
            DB::table('social_platforms')->where('code', $code)->update(['icon' => $icon]);
        }

        // 2. the general links table
        Schema::create('social_links', function (Blueprint $t) {
            $t->id();
            $t->morphs('linkable');   // linkable_type + linkable_id (+ index)
            $t->foreignId('social_platform_id')->constrained('social_platforms')->restrictOnDelete();
            $t->string('url', 2048);
            $t->foreignId('UID')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('LUID')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->softDeletes();
        });

        // 3. copy the existing links
        $this->copy('company_social_links', 'company_id', \App\Models\Company::class);
        $this->copy('candidate_social_links', 'candidate_id', \App\Models\CandidateProfile::class);

        // 4. the two old tables are no longer needed
        Schema::dropIfExists('company_social_links');
        Schema::dropIfExists('candidate_social_links');
    }

    public function down(): void
    {
        throw new \RuntimeException('This migration is not reversible. Restore the DB backup instead.');
    }

    private function copy(string $table, string $ownerColumn, string $type): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }
        DB::table('social_links')->insertUsing(
            ['linkable_type', 'linkable_id', 'social_platform_id', 'url', 'UID', 'LUID', 'created_at', 'updated_at', 'deleted_at'],
            DB::table($table)->select(
                DB::raw(DB::getPdo()->quote($type)),
                $ownerColumn, 'social_platform_id', 'url', 'UID', 'LUID', 'created_at', 'updated_at', 'deleted_at'
            )
        );
    }
};
