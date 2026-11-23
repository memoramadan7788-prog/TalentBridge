<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\HasRecordStatus;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CandidateCv extends Model
{
    use SoftDeletes, Auditable, HasRecordStatus;


    protected $table = 'candidate_cvs';
    protected $guarded = [];

    protected $casts = [
        'is_default' => 'boolean',
        'ats_score' => 'decimal:2',
        'ats_strengths' => 'array',
        'ats_weaknesses' => 'array',
        'uploaded_at' => 'datetime',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(CandidateProfile::class, 'candidate_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'cv_id');
    }

    /** the AI scan of this CV (score, strengths, weaknesses) */
    public function scan(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(CvScan::class, 'cv_id');
    }

    /** job titles the AI suggested for this CV */
    public function matchedJobs(): HasMany
    {
        return $this->hasMany(MatchedJob::class, 'cv_id')->orderBy('sort_order');
    }

    public function cvMatchResults(): HasMany
    {
        return $this->hasMany(CvMatchResult::class, 'cv_id');
    }

    public function jobMatches(): HasMany
    {
        return $this->hasMany(JobMatch::class, 'cv_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }
}
