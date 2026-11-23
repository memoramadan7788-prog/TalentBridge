<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidate_cvs', function (Blueprint $table): void {
            if (! Schema::hasColumn('candidate_cvs', 'ats_score')) {
                $table->decimal('ats_score', 5, 2)->nullable();
            }
            if (! Schema::hasColumn('candidate_cvs', 'ats_strengths')) {
                $table->json('ats_strengths')->nullable();
            }
            if (! Schema::hasColumn('candidate_cvs', 'ats_weaknesses')) {
                $table->json('ats_weaknesses')->nullable();
            }
        });

        Schema::dropIfExists('ai_review_decisions');
    }

    public function down(): void
    {
        Schema::table('candidate_cvs', function (Blueprint $table): void {
            $columns = array_values(array_filter(
                ['ats_score', 'ats_strengths', 'ats_weaknesses'],
                fn (string $column): bool => Schema::hasColumn('candidate_cvs', $column)
            ));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });

        Schema::create('ai_review_decisions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('match_result_id')->constrained('cv_match_results');
            $table->foreignId('reviewer_id')->constrained('users');
            $table->enum('decision', ['accept', 'override'])->default('accept');
            $table->text('reason')->nullable();
            $table->timestamp('decided_at')->useCurrent()->useCurrentOnUpdate();
            $table->foreignId('UID')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('LUID')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }
};
