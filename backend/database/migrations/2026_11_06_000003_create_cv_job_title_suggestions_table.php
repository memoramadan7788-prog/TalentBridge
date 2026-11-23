<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cv_job_title_suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cv_scan_id')->constrained('cv_scans')->cascadeOnDelete();
            $table->string('title', 255);
            $table->string('normalized_title', 255);
            $table->decimal('confidence', 5, 2)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('status', 20)->default('pending');
            $table->foreignId('job_title_id')->nullable()->constrained('job_titles')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->unique(['cv_scan_id', 'normalized_title']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cv_job_title_suggestions');
    }
};
