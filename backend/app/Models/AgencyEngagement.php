<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasRecordVisibility;

class AgencyEngagement extends Model
{
    use \App\Models\Concerns\HasLookupAttributes;

    use SoftDeletes, HasRecordVisibility;

    protected $table = 'agency_engagements';
    protected $guarded = [];

    protected $casts = [
        'terms' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'agency_company_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'client_company_id');
    }

    public function recruiter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recruiter_user_id');
    }

    public function clientOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_owner_user_id');
    }

    public function createdByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id');
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(EngagementReview::class, 'engagement_id')->latest('week_start');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AgencyEngagementSubmission::class, 'engagement_id')->latest('submitted_at');
    }

    protected function lookups(): array
    {
        return [
            'status' => ['status_id', Status::class, 'draft', 'engagement'],
        ];
    }

    public function engagementStatus(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }
}
