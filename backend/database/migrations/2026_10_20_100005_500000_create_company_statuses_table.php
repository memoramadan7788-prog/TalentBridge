<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('company_statuses')) {
            Schema::create('company_statuses', function (Blueprint $table) {
                $table->unsignedTinyInteger('id')->primary();
                $table->string('name', 30)->unique();
                $table->timestamps();
            });
        }

        DB::table('company_statuses')->insertOrIgnore([
            ['id' => 1, 'name' => 'pending', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'approved', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'rejected', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'name' => 'suspended', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('company_statuses');
    }
};
