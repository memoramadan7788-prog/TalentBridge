<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EngagementReview extends Model
{
    protected $table = 'engagement_reviews';
    protected $guarded = [];

    protected $casts = [
        'week_start' => 'date',
        'statistics' => 'array',
    ];

    public function engagement(): BelongsTo
    {
        return $this->belongsTo(AgencyEngagement::class, 'engagement_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'reviewed_by_admin_id');
    }
}
