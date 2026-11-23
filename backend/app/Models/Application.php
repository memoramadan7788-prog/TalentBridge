<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\HasRecordVisibility;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    use \App\Models\Concerns\HasLookupAttributes;

    use SoftDeletes, Auditable, HasRecordVisibility;

    protected $table = 'applications';
    protected $guarded = [];

    protected $casts = [
        'expected_salary' => 'decimal:2',
        'keep_in_talent_pool' => 'boolean',
        'consent_at' => 'datetime',
        'applied_at' => 'datetime',
        'withdrawn_at' => 'datetime',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class, 'job_id');
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(CandidateProfile::class, 'candidate_id');
    }

    public function candidateProfile(): BelongsTo
    {
        return $this->candidate();
    }

    public function applicationStatus(): BelongsTo
    {
        return $this->belongsTo(ApplicationStatus::class, 'application_status_id');
    }

    /**
     * Keep the existing API field while storing the normalized status FK.
     */
    public function getStatusAttribute(mixed $value): ?string
    {
        return $value ?? $this->applicationStatus?->name;
    }

    /**
     * Accept the legacy status names and persist only application_status_id.
     */
    public function setStatusAttribute(string|int|null $value): void
    {
        $this->attributes['application_status_id'] = ApplicationStatus::idFor($value);
    }

    public function cv(): BelongsTo
    {
        return $this->belongsTo(CandidateCv::class, 'cv_id');
    }

    public function candidateCv(): BelongsTo
    {
        return $this->cv();
    }

    public function applicationStatusHistory(): HasMany
    {
        return $this->hasMany(ApplicationStatusHistory::class, 'application_id');
    }

    public function applicationStatusHistories(): HasMany
    {
        return $this->applicationStatusHistory();
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class, 'application_id');
    }

    public function interviews(): HasMany
    {
        return $this->hasMany(Interview::class, 'application_id');
    }

    public function examAttempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class, 'application_id');
    }

    public function cvMatchResults(): HasMany
    {
        return $this->hasMany(CvMatchResult::class, 'application_id');
    }

    public function screeningAnswers(): HasMany
    {
        return $this->hasMany(ApplicationAnswer::class, 'application_id');
    }

    protected function lookups(): array
    {
        return [
            'source' => ['application_source_id', ApplicationSource::class, null],
            'decision' => ['application_decision_id', ApplicationDecision::class, null],
        ];
    }

    public function recordStatus(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function applicationSource(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ApplicationSource::class, 'application_source_id');
    }

    public function applicationDecision(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ApplicationDecision::class, 'application_decision_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ApplicationAnswer::class, 'application_id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(ApplicationNote::class, 'application_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
