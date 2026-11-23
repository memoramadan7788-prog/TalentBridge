<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cv_scans', function (Blueprint $table): void {
            $table->json('extracted_data')->nullable();
            $table->longText('extracted_text')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('cv_scans', function (Blueprint $table): void {
            $table->dropColumn(['extracted_data', 'extracted_text']);
        });
    }
};
