<?php

namespace App\Models;

use App\Models\Concerns\HasLookupAttributes;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

/**
 * The AI scan of ONE CV (no job posting involved): score, strengths, weaknesses
 * and the job titles it suggests (matched_jobs).
 */
class CvScan extends Model
{
    use Auditable, SoftDeletes;
    use HasLookupAttributes;

    public const PENDING = 'pending';

    public const OK = 'ok';

    public const FAILED = 'failed';

    public const TIMEOUT = 'timeout';

    protected $table = 'cv_scans';

    protected $guarded = [];

    protected $casts = [
        'score' => 'decimal:2',
        'finished_at' => 'datetime',
        'extracted_data' => 'array',
    ];

    public function cv(): BelongsTo
    {
        return $this->belongsTo(CandidateCv::class, 'cv_id');
    }

    public function points(): HasMany
    {
        return $this->hasMany(CvScanPoint::class, 'cv_scan_id')->orderBy('sort_order');
    }

    public function titleSuggestions(): HasMany
    {
        return $this->hasMany(CvJobTitleSuggestion::class, 'cv_scan_id')->orderBy('sort_order');
    }

    public function strengths(): HasMany
    {
        return $this->points()->where('cv_scan_point_type_id', CvScanPointType::idFor('strength'));
    }

    public function weaknesses(): HasMany
    {
        return $this->points()->where('cv_scan_point_type_id', CvScanPointType::idFor('weakness'));
    }

    /** $scan->status = 'ok' ... (statuses.type = cv_run) */
    protected function lookups(): array
    {
        return [
            'status' => ['status_id', Status::class, self::PENDING, 'cv_run'],
        ];
    }

    public function scanStatus(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    /**
     * Save what the AI returned for this CV.
     *
     * Expected shape (all keys optional except score):
     *   [
     *     'score'          => 78.5,
     *     'summary'        => '...',
     *     'strengths'      => ['Strong Laravel experience', ...],
     *     'weaknesses'     => ['No cloud experience', ...],
     *     'suggested_jobs' => ['Laravel Backend Developer', ['title' => 'DevOps Engineer', 'confidence' => 61], ...],
     *     'model_version'  => 'llama3.1:8b',
     *     'request_id'     => '...',
     *   ]
     *
     * Existing job titles are linked immediately. Unknown titles are held for admin approval.
     * Calling it again for the same CV replaces the previous points and suggestions.
     */
    public function storeResult(array $result): void
    {
        DB::transaction(function () use ($result) {
            $this->fill([
                'status' => self::OK,
                'score' => $result['score'] ?? null,
                'summary' => $result['summary'] ?? null,
                'model_version' => $result['model_version'] ?? null,
                'extracted_data' => $result['extracted_data'] ?? null,
                'extracted_text' => $result['extracted_text'] ?? null,
                'request_id' => $result['request_id'] ?? null,
                'error_message' => null,
                'finished_at' => now(),
            ])->save();

            // strengths / weaknesses
            $this->points()->delete();
            foreach (['strength' => 'strengths', 'weakness' => 'weaknesses'] as $type => $key) {
                $typeId = CvScanPointType::idFor($type);
                foreach (array_values(array_filter((array) ($result[$key] ?? []), 'filled')) as $i => $text) {
                    CvScanPoint::create([
                        'cv_scan_id' => $this->id,
                        'cv_scan_point_type_id' => $typeId,
                        'content' => trim((string) $text),
                        'sort_order' => $i,
                    ]);
                }
            }

            // Existing titles can be linked. AI generated taxonomy entries require approval.
            $keep = [];
            $normalizedTitles = [];
            foreach (array_values((array) ($result['suggested_jobs'] ?? [])) as $i => $item) {
                $title = trim((string) (is_array($item) ? ($item['title'] ?? '') : $item));
                if ($title === '') {
                    continue;
                }
                $title = mb_substr($title, 0, 255);
                $normalized = mb_strtolower($title);
                $normalizedTitles[] = $normalized;
                $titleId = JobTitle::query()->whereRaw('LOWER(name) = ?', [$normalized])->value('id');
                if (! $titleId) {
                    CvJobTitleSuggestion::updateOrCreate(
                        ['cv_scan_id' => $this->id, 'normalized_title' => $normalized],
                        ['title' => $title, 'confidence' => is_array($item) ? ($item['confidence'] ?? null) : null, 'sort_order' => $i, 'status' => 'pending']
                    );
                    continue;
                }
                $keep[] = $titleId;
                MatchedJob::updateOrCreate(
                    ['cv_id' => $this->cv_id, 'job_title_id' => $titleId],
                    ['confidence' => is_array($item) ? ($item['confidence'] ?? null) : null, 'sort_order' => $i]
                );
            }
            CvJobTitleSuggestion::where('cv_scan_id', $this->id)->whereNotIn('normalized_title', $normalizedTitles ?: [''])->where('status', 'pending')->delete();
            MatchedJob::where('cv_id', $this->cv_id)->whereNotIn('job_title_id', $keep)->delete();
        });
    }

    public function markFailed(string $message, bool $timeout = false): void
    {
        $this->fill([
            'status' => $timeout ? self::TIMEOUT : self::FAILED,
            'error_message' => $message,
            'finished_at' => now(),
        ])->save();
    }
}
