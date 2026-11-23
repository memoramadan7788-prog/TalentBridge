<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create social_media_platforms table
        Schema::create('social_media_platforms', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('icon')->nullable();
            $table->string('base_domain')->nullable()->comment('for validation only');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Drop old empty candidate_social_links and company_social_links if they exist
        Schema::dropIfExists('candidate_social_links');
        Schema::dropIfExists('company_social_links');
        Schema::dropIfExists('company_social_media');

        // 3. Create candidate_social_links matching ERD: USERS ||--o{ CANDIDATE_SOCIAL_LINKS
        Schema::create('candidate_social_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('platform_id')->constrained('social_media_platforms')->cascadeOnDelete();
            $table->string('url', 2048)->comment('full https URL');
            $table->timestamps();

            $table->unique(['candidate_id', 'platform_id'], 'candidate_platform_unique');
        });

        // 4. Create company_social_media matching ERD: COMPANIES ||--o{ COMPANY_SOCIAL_MEDIA
        Schema::create('company_social_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('platform_id')->constrained('social_media_platforms')->cascadeOnDelete();
            $table->string('url', 2048)->comment('full https URL');
            $table->timestamps();

            $table->unique(['company_id', 'platform_id'], 'company_platform_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_social_media');
        Schema::dropIfExists('candidate_social_links');
        Schema::dropIfExists('social_media_platforms');
    }
};
