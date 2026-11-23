<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgencyEngagementSubmission extends Model
{
    use \App\Models\Concerns\HasLookupAttributes;

    protected $table = 'agency_engagement_submissions';
    protected $guarded = [];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function engagement(): BelongsTo
    {
        return $this->belongsTo(AgencyEngagement::class, 'engagement_id');
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class, 'application_id');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'reviewed_by_admin_id');
    }

    protected function lookups(): array
    {
        return [
            'status' => ['status_id', Status::class, 'pending_review', 'submission'],
        ];
    }

    public function submissionStatus(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }
}
