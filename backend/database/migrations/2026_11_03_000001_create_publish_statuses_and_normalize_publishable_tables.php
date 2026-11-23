<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = ['jobs', 'exams', 'questions'];

    public function up(): void
    {
        Schema::create('publish_statuses', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
            $table->string('name', 30)->unique();
            $table->timestamps();
        });

        DB::table('publish_statuses')->insert([
            ['id' => 1, 'name' => 'draft', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'published', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'closed', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'name' => 'archived', 'created_at' => now(), 'updated_at' => now()],
        ]);

        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->unsignedTinyInteger('publish_status_id')->default(1)->after('id');
                $table->foreign('publish_status_id')->references('id')->on('publish_statuses')->restrictOnDelete();
                $table->index('publish_status_id');
            });

            DB::table($tableName)->orderBy('id')->chunkById(500, function ($rows) use ($tableName): void {
                foreach ($rows as $row) {
                    $statusId = match ((string) ($row->status ?? 'draft')) {
                        'published' => 2,
                        'closed' => 3,
                        'archived' => 4,
                        default => 1,
                    };

                    DB::table($tableName)->where('id', $row->id)->update([
                        'publish_status_id' => $statusId,
                    ]);
                }
            });

            // Keep the legacy status until normalize_main_tables moves it into
            // the shared status_id lookup. publish_status_id is a separate concern.
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->tables) as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['publish_status_id']);
                $table->dropIndex(['publish_status_id']);
                $table->dropColumn('publish_status_id');
            });
        }

        Schema::dropIfExists('publish_statuses');
    }
};
