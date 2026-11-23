<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $statuses = [
        1 => 'sent',
        2 => 'accepted',
        3 => 'rejected',
        4 => 'expired',
    ];

    public function up(): void
    {
        Schema::create('offer_statuses', function (Blueprint $table): void {
            $table->unsignedTinyInteger('id')->primary();
            $table->string('name', 30)->unique();
            $table->timestamps();
        });

        $now = now();
        DB::table('offer_statuses')->insert(array_map(
            static fn (int $id, string $name): array => [
                'id' => $id,
                'name' => $name,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            array_keys($this->statuses),
            $this->statuses
        ));

        Schema::table('offers', function (Blueprint $table): void {
            $table->unsignedTinyInteger('offer_status_id')->default(1)->after('id');
            $table->index('offer_status_id');
        });

        // Migrate existing enum values before removing the duplicated text column.
        if (Schema::hasColumn('offers', 'status')) {
            DB::table('offers')->orderBy('id')->chunkById(500, function ($offers): void {
                foreach ($offers as $offer) {
                    $statusId = array_search((string) ($offer->status ?? 'sent'), $this->statuses, true);

                    DB::table('offers')
                        ->where('id', $offer->id)
                        ->update(['offer_status_id' => $statusId ?: 1]);
                }
            });
        }

        // The unified-status migration has already replaced the old status string
        // with statuses(type=offer). Copy it into the dedicated workflow FK.
        if (! Schema::hasColumn('offers', 'status') && Schema::hasColumn('offers', 'status_id') && Schema::hasTable('statuses')) {
            DB::table('offers')->orderBy('offers.id')->chunkById(500, function ($offers): void {
                foreach ($offers as $offer) {
                    $code = DB::table('statuses')->where('id', $offer->status_id)->where('type', 'offer')->value('code');
                    if ($code !== null) {
                        $statusId = array_search($code, $this->statuses, true);
                        DB::table('offers')->where('id', $offer->id)->update(['offer_status_id' => $statusId ?: 1]);
                    }
                }
            });
        }

        Schema::table('offers', function (Blueprint $table): void {
            $table->foreign('offer_status_id')
                ->references('id')
                ->on('offer_statuses')
                ->restrictOnDelete();
        });

        if (Schema::hasColumn('offers', 'status')) {
            Schema::table('offers', function (Blueprint $table): void {
                $table->dropIndex(['status']);
                $table->dropColumn('status');
            });
        }
    }

    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table): void {
            $table->string('status', 30)->default('sent')->after('offer_status_id');
            $table->index('status');
        });

        DB::table('offers')->orderBy('id')->chunkById(500, function ($offers): void {
            foreach ($offers as $offer) {
                $status = DB::table('offer_statuses')
                    ->where('id', $offer->offer_status_id)
                    ->value('name') ?? 'sent';

                DB::table('offers')
                    ->where('id', $offer->id)
                    ->update(['status' => $status]);
            }
        });

        Schema::table('offers', function (Blueprint $table): void {
            $table->dropForeign(['offer_status_id']);
            $table->dropIndex(['offer_status_id']);
            $table->dropColumn('offer_status_id');
        });

        Schema::dropIfExists('offer_statuses');
    }
};
