<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('statuses')) {
            Schema::create('statuses', function (Blueprint $table): void {
                $table->unsignedTinyInteger('id')->primary();
                $table->string('name')->unique();
                $table->timestamps();
            });
        }
        DB::table('statuses')->insertOrIgnore([
            ['id' => 1, 'name' => 'active', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'deactive', 'created_at' => now(), 'updated_at' => now()],
        ]);

        if (! Schema::hasTable('visibilities')) {
            Schema::create('visibilities', function (Blueprint $table): void {
                $table->unsignedTinyInteger('id')->primary();
                $table->string('name')->unique();
                $table->timestamps();
            });
        }
        DB::table('visibilities')->insertOrIgnore([
            ['id' => 1, 'name' => 'public', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'private', 'created_at' => now(), 'updated_at' => now()],
        ]);

        if (! Schema::hasTable('countries')) {
            Schema::create('countries', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('code', 2)->unique();
                $this->addStatusRelation($table);
                $table->foreignId('UID')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('LUID')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();
            });
        } elseif (! Schema::hasColumn('countries', 'status_id')) {
            Schema::table('countries', function (Blueprint $table): void {
                $this->addStatusRelation($table);
            });
        }

        if (! Schema::hasColumn('users', 'country_id')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('cities')) {
            Schema::create('cities', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('country_id')->constrained('countries')->cascadeOnDelete();
                $table->string('name');
                $table->string('code', 10)->nullable();
                $table->foreignId('UID')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('LUID')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();
                $table->unique(['country_id', 'name']);
            });
        }

        if (! Schema::hasTable('areas')) {
            Schema::create('areas', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('city_id')->constrained('cities')->cascadeOnDelete();
                $table->string('name');
                $table->foreignId('UID')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('LUID')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();
                $table->unique(['city_id', 'name']);
            });
        }
    }

    private function addStatusRelation(Blueprint $table): void
    {
        $statusIdType = strtolower((string) Schema::getColumnType('statuses', 'id'));

        if ($statusIdType === 'bigint') {
            $table->unsignedBigInteger('status_id')->default(1);
        } else {
            $table->unsignedTinyInteger('status_id')->default(1);
        }

        $table->foreign('status_id')->references('id')->on('statuses')->restrictOnDelete();
        $table->index('status_id');
    }

    public function down(): void
    {
        Schema::dropIfExists('areas');
        Schema::dropIfExists('cities');
        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('country_id');
        });
        Schema::dropIfExists('countries');
        Schema::dropIfExists('visibilities');
        Schema::dropIfExists('statuses');
    }
};
