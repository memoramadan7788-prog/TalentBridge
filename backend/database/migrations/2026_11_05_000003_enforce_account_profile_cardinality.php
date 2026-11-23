<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicates = DB::table('companies')
            ->select('owner_user_id')
            ->whereNotNull('owner_user_id')
            ->groupBy('owner_user_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('owner_user_id')
            ->all();

        if ($duplicates !== []) {
            throw new RuntimeException(
                'Cannot enforce the company account 1:1 relation. Duplicate owner_user_id values: '
                . implode(', ', $duplicates)
            );
        }

        $hasUniqueOwnerIndex = collect(Schema::getIndexes('companies'))
            ->contains(fn (array $index): bool => ($index['name'] ?? null) === 'companies_owner_user_id_unique');

        if (! $hasUniqueOwnerIndex) {
            Schema::table('companies', function (Blueprint $table): void {
                $table->unique('owner_user_id', 'companies_owner_user_id_unique');
            });
        }
    }

    public function down(): void
    {
        $hasUniqueOwnerIndex = collect(Schema::getIndexes('companies'))
            ->contains(fn (array $index): bool => ($index['name'] ?? null) === 'companies_owner_user_id_unique');

        if ($hasUniqueOwnerIndex) {
            Schema::table('companies', function (Blueprint $table): void {
                $table->dropUnique('companies_owner_user_id_unique');
            });
        }
    }
};
