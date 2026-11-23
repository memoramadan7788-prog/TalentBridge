<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A job title the AI suggested for a CV. */
class MatchedJob extends Model
{
    protected $table = 'matched_jobs';
    protected $guarded = [];

    protected $casts = [
        'confidence' => 'decimal:2',
    ];

    public function cv(): BelongsTo
    {
        return $this->belongsTo(CandidateCv::class, 'cv_id');
    }

    public function jobTitle(): BelongsTo
    {
        return $this->belongsTo(JobTitle::class, 'job_title_id');
    }
}
