<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_matches', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('job_id')->constrained('jobs')->cascadeOnDelete();
            $table->foreignId('cv_id')->constrained('candidate_cvs')->cascadeOnDelete();
            $table->decimal('score', 5, 2)->nullable();
            $table->string('recommendation')->nullable();
            $table->json('matched_skills')->nullable();
            $table->json('missing_skills')->nullable();
            $table->json('strengths')->nullable();
            $table->json('weaknesses')->nullable();
            $table->text('summary')->nullable();
            $table->string('model_version')->nullable();
            $table->enum('run_status', ['pending', 'ok', 'failed'])->default('pending')->index();
            $table->text('error_message')->nullable();
            $table->timestamp('matched_at')->nullable();
            $table->foreignId('UID')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('LUID')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['job_id', 'cv_id']);
            $table->index(['cv_id', 'run_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_matches');
    }
};
