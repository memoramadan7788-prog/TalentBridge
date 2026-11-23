<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $statuses = [
        1 => ['name' => 'submitted', 'label' => 'Pending'],
        2 => ['name' => 'screening', 'label' => 'Under review'],
        3 => ['name' => 'shortlisted', 'label' => 'Shortlisted'],
        4 => ['name' => 'interviewing', 'label' => 'Scheduling meeting'],
        5 => ['name' => 'offered', 'label' => 'Offer sent'],
        6 => ['name' => 'hired', 'label' => 'Hired'],
        7 => ['name' => 'rejected', 'label' => 'Rejected'],
        8 => ['name' => 'withdrawn', 'label' => 'Withdrawn'],
    ];

    public function up(): void
    {
        Schema::create('application_statuses', function (Blueprint $table): void {
            $table->unsignedTinyInteger('id')->primary();
            $table->string('name', 30)->unique();
            $table->string('label', 60);
            $table->timestamps();
        });

        $now = now();
        DB::table('application_statuses')->insert(array_map(
            static fn (int $id, array $status): array => [
                'id' => $id,
                'name' => $status['name'],
                'label' => $status['label'],
                'created_at' => $now,
                'updated_at' => $now,
            ],
            array_keys($this->statuses),
            $this->statuses
        ));

        Schema::table('applications', function (Blueprint $table): void {
            $table->unsignedTinyInteger('application_status_id')->default(1)->after('candidate_id');
            $table->index('application_status_id');
        });

        if (Schema::hasColumn('applications', 'status')) {
            DB::table('applications')->orderBy('id')->chunkById(500, function ($applications): void {
                foreach ($applications as $application) {
                    $statusId = $this->statusIdFor($application->status);

                    DB::table('applications')
                        ->where('id', $application->id)
                        ->update(['application_status_id' => $statusId]);
                }
            });
        }

        // The preceding unified-status migration already removed the legacy string
        // column and stored the workflow code in statuses(type=application). Restore
        // that value into the dedicated workflow FK instead of leaving every row at 1.
        if (! Schema::hasColumn('applications', 'status') && Schema::hasColumn('applications', 'status_id') && Schema::hasTable('statuses')) {
            DB::table('applications')->orderBy('applications.id')->chunkById(500, function ($applications): void {
                foreach ($applications as $application) {
                    $code = DB::table('statuses')->where('id', $application->status_id)->where('type', 'application')->value('code');
                    if ($code === null) {
                        continue;
                    }
                    $statusId = match ($code) {
                        'under_review' => 2,
                        'offer' => 5,
                        default => $this->statusIdFor($code),
                    };
                    DB::table('applications')->where('id', $application->id)->update(['application_status_id' => $statusId]);
                }
            });
        }

        Schema::table('applications', function (Blueprint $table): void {
            $table->foreign('application_status_id')
                ->references('id')
                ->on('application_statuses')
                ->restrictOnDelete();
        });

        if (Schema::hasColumn('applications', 'status')) {
            Schema::table('applications', function (Blueprint $table): void {
                $table->dropIndex(['status']);
                $table->dropColumn('status');
            });
        }
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table): void {
            $table->string('status', 30)->default('submitted')->after('application_status_id');
            $table->index('status');
        });

        DB::table('applications')->orderBy('id')->chunkById(500, function ($applications): void {
            foreach ($applications as $application) {
                $status = DB::table('application_statuses')
                    ->where('id', $application->application_status_id)
                    ->value('name') ?? 'submitted';

                DB::table('applications')
                    ->where('id', $application->id)
                    ->update(['status' => $status]);
            }
        });

        Schema::table('applications', function (Blueprint $table): void {
            $table->dropForeign(['application_status_id']);
            $table->dropIndex(['application_status_id']);
            $table->dropColumn('application_status_id');
        });

        Schema::dropIfExists('application_statuses');
    }

    private function statusIdFor(?string $status): int
    {
        return match (strtolower(trim((string) $status))) {
            'pending', 'applied', 'submitted' => 1,
            'under_review', 'screening' => 2,
            'shortlisted' => 3,
            'scheduling_meeting', 'interview', 'interviewing' => 4,
            'offer', 'offered' => 5,
            'hired' => 6,
            'rejected' => 7,
            'withdrawn' => 8,
            default => 1,
        };
    }
};
