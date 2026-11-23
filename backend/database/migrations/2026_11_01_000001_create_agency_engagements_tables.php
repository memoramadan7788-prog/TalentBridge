
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create agency engagements
        Schema::create('agency_engagements', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('status_id')->default(1);
            $table->foreign('status_id')->references('id')->on('statuses')->restrictOnDelete();
            $table->index('status_id');
            $table->unsignedTinyInteger('visibility_id')->default(1);
            $table->foreign('visibility_id')->references('id')->on('visibilities')->restrictOnDelete();
            $table->index('visibility_id');

            $table->foreignId('agency_company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->foreignId('client_company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->foreignId('recruiter_user_id')
                ->constrained('users');

            $table->foreignId('client_owner_user_id')
                ->constrained('users');

            $table->foreignId('created_by_admin_id')
                ->constrained('admins');

            $table->foreignId('job_id')
                ->nullable()
                ->constrained('jobs')
                ->nullOnDelete();

            $table->string('title');
            $table->text('brief')->nullable();
            $table->json('terms')->nullable();

            $table->string('status')->default('draft')->index();

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->string('weekly_review_day')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['agency_company_id', 'status']);
            $table->index(['client_company_id', 'status']);
        });

        // 2. Create engagement reviews
        Schema::create('engagement_reviews', function (Blueprint $table) {
            $table->id();

            $table->foreignId('engagement_id')
                ->constrained('agency_engagements')
                ->cascadeOnDelete();

            $table->foreignId('reviewed_by_admin_id')
                ->constrained('admins');

            $table->date('week_start');

            $table->json('statistics')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['engagement_id', 'week_start']);
        });

        // 3. Create agency engagement submissions
        Schema::create('agency_engagement_submissions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('engagement_id')
                ->constrained('agency_engagements')
                ->cascadeOnDelete();

            $table->foreignId('application_id')
                ->nullable()
                ->constrained('applications')
                ->nullOnDelete();

            $table->foreignId('submitted_by_user_id')
                ->constrained('users');

            $table->foreignId('reviewed_by_admin_id')
                ->nullable()
                ->constrained('admins')
                ->nullOnDelete();

            $table->string('status')
                ->default('pending_review')
                ->index();

            $table->text('review_notes')->nullable();

            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            $table->unique(['engagement_id', 'application_id'], 'agency_sub_engagement_application_unique');
            $table->index(['engagement_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agency_engagement_submissions');
        Schema::dropIfExists('engagement_reviews');
        Schema::dropIfExists('agency_engagements');

        
    }
};
