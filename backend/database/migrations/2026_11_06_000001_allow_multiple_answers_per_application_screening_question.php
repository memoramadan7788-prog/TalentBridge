<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $indexes = collect(Schema::getIndexes('application_answers'));
        $unique = $indexes->first(fn (array $index): bool => ($index['columns'] ?? []) === ['application_id', 'job_screening_question_id'] && ($index['unique'] ?? false));

        if ($unique) {
            Schema::table('application_answers', fn (Blueprint $table) => $table->dropUnique($unique['name']));
        }

        $hasLookupIndex = collect(Schema::getIndexes('application_answers'))
            ->contains(fn (array $index): bool => ($index['columns'] ?? []) === ['application_id', 'job_screening_question_id']);

        if (! $hasLookupIndex) {
            Schema::table('application_answers', fn (Blueprint $table) => $table->index(['application_id', 'job_screening_question_id'], 'app_answers_app_question_index'));
        }
    }

    public function down(): void
    {
        $hasDuplicates = DB::table('application_answers')
            ->select('application_id', 'job_screening_question_id')
            ->groupBy('application_id', 'job_screening_question_id')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($hasDuplicates) {
            throw new RuntimeException('Cannot restore one answer per question while duplicate answers exist.');
        }

        Schema::table('application_answers', function (Blueprint $table): void {
            $table->dropIndex('app_answers_app_question_index');
            $table->unique(['application_id', 'job_screening_question_id'], 'app_answers_app_question_unique');
        });
    }
};
