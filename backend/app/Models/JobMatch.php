<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class JobMatch extends Model
{
    use SoftDeletes, Auditable;

    public const PENDING = 'pending';
    public const OK = 'ok';
    public const FAILED = 'failed';

    protected $table = 'job_matches';
    protected $guarded = [];

    protected $casts = [
        'score' => 'decimal:2',
        'matched_skills' => 'array',
        'missing_skills' => 'array',
        'strengths' => 'array',
        'weaknesses' => 'array',
        'matched_at' => 'datetime',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class, 'job_id');
    }

    public function cv(): BelongsTo
    {
        return $this->belongsTo(CandidateCv::class, 'cv_id');
    }
}
