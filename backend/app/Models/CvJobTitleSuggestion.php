<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CvJobTitleSuggestion extends Model
{
    protected $table = 'cv_job_title_suggestions';

    protected $guarded = [];

    protected $casts = ['confidence' => 'decimal:2', 'reviewed_at' => 'datetime'];

    public function scan(): BelongsTo
    {
        return $this->belongsTo(CvScan::class, 'cv_scan_id');
    }
}
