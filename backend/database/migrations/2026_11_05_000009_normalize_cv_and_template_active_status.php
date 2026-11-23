<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('candidate_cvs')) {
            if (! Schema::hasColumn('candidate_cvs', 'is_default')) {
                Schema::table('candidate_cvs', function (Blueprint $table): void {
                    $table->boolean('is_default')->default(false);
                });
            }
            $this->ensureStatusRelation('candidate_cvs');

            if (Schema::hasColumn('candidate_cvs', 'is_active')) {
                DB::table('candidate_cvs')->update([
                    'is_default' => DB::raw('is_active'),
                    'status_id' => DB::raw('CASE WHEN is_active = 1 THEN 1 ELSE 2 END'),
                ]);
                Schema::table('candidate_cvs', function (Blueprint $table): void {
                    $table->dropColumn('is_active');
                });
            }

            if (Schema::hasColumn('candidate_cvs', 'version')) {
                $hasCandidateIdIndex = collect(Schema::getIndexes('candidate_cvs'))
                    ->contains(fn (array $index): bool => ($index['columns'] ?? []) === ['candidate_id']);

                if (! $hasCandidateIdIndex) {
                    Schema::table('candidate_cvs', fn (Blueprint $table) => $table->index('candidate_id'));
                }

                Schema::table('candidate_cvs', function (Blueprint $table): void {
                    $table->dropUnique(['candidate_id', 'version']);
                    $table->dropColumn('version');
                });
            }

            Schema::table('candidate_cvs', function (Blueprint $table): void {
                $table->index(['candidate_id', 'is_default']);
            });
        }

        if (Schema::hasTable('offer_letter_templates')) {
            $this->ensureStatusRelation('offer_letter_templates');

            if (Schema::hasColumn('offer_letter_templates', 'is_active')) {
                DB::table('offer_letter_templates')->update([
                    'status_id' => DB::raw('CASE WHEN is_active = 1 THEN 1 ELSE 2 END'),
                ]);
                Schema::table('offer_letter_templates', function (Blueprint $table): void {
                    $table->dropColumn('is_active');
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('offer_letter_templates') && Schema::hasColumn('offer_letter_templates', 'status_id')) {
            Schema::table('offer_letter_templates', function (Blueprint $table): void {
                $table->dropForeign(['status_id']);
                $table->dropIndex(['status_id']);
                $table->dropColumn('status_id');
                $table->boolean('is_active')->default(true);
            });
        }

        if (Schema::hasTable('candidate_cvs')) {
            Schema::table('candidate_cvs', function (Blueprint $table): void {
                if (Schema::hasColumn('candidate_cvs', 'status_id')) {
                    $table->dropForeign(['status_id']);
                    $table->dropIndex(['status_id']);
                    $table->dropColumn('status_id');
                }
                if (Schema::hasColumn('candidate_cvs', 'is_default')) {
                    $table->boolean('is_active')->default(true);
                    $table->unsignedSmallInteger('version')->default(1);
                    $table->unique(['candidate_id', 'version']);
                    $table->dropIndex(['candidate_id', 'is_default']);
                    $table->dropColumn('is_default');
                }
            });
        }
    }

    private function ensureStatusRelation(string $tableName): void
    {
        if (! Schema::hasColumn($tableName, 'status_id')) {
            Schema::table($tableName, fn (Blueprint $table) => $table->unsignedBigInteger('status_id')->default(1));
        } else {
            Schema::table($tableName, fn (Blueprint $table) => $table->unsignedBigInteger('status_id')->default(1)->nullable(false)->change());
        }

        $hasForeignKey = collect(Schema::getForeignKeys($tableName))
            ->contains(fn (array $foreignKey): bool => in_array('status_id', $foreignKey['columns'] ?? [], true));

        if (! $hasForeignKey) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->foreign('status_id')->references('id')->on('statuses')->restrictOnDelete();
                $table->index('status_id');
            });
        }
    }
};
