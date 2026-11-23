<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\HasRecordVisibility;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CandidateProfile extends Model
{
    use \App\Models\Concerns\HasLookupAttributes;

    use SoftDeletes, Auditable, HasRecordVisibility;

    protected $table = 'candidate_profiles';
    protected $guarded = [];

    protected $casts = [
        'has_no_experience' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Generic active/deactive status for the candidate profile.
     * One status can belong to many candidate profiles.
     */
    public function status(): BelongsTo
    {
        return $this->recordStatus();
    }

    public function candidateExperience(): HasMany
    {
        return $this->hasMany(CandidateExperience::class, 'candidate_id');
    }

    public function candidateExperiences(): HasMany
    {
        return $this->candidateExperience();
    }

    public function candidateEducation(): HasMany
    {
        return $this->hasMany(CandidateEducation::class, 'candidate_id');
    }

    public function candidateEducations(): HasMany
    {
        return $this->candidateEducation();
    }

    public function candidateCourses(): HasMany
    {
        return $this->hasMany(CandidateCourse::class, 'candidate_id');
    }

    public function socialLinks(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(SocialLink::class, 'linkable');
    }

    public function candidateCvs(): HasMany
    {
        return $this->hasMany(CandidateCv::class, 'candidate_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'candidate_id');
    }

    public function applicationDrafts(): HasMany
    {
        return $this->hasMany(CandidateApplicationDraft::class, 'candidate_id');
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'candidate_skills', 'candidate_id', 'skill_id')->withPivot('skill_level_id');
    }

    public function savedJobs(): BelongsToMany
    {
        return $this->belongsToMany(Job::class, 'candidate_saved_jobs', 'candidate_id', 'job_id')
            ->withTimestamps();
    }

    public function savedSearches(): HasMany
    {
        return $this->hasMany(CandidateSavedSearch::class, 'candidate_id');
    }

    public function hiddenJobs(): HasMany
    {
        return $this->hasMany(CandidateHiddenJob::class, 'candidate_id');
    }

    public function onboarding(): HasOne
    {
        return $this->hasOne(CandidateOnboarding::class, 'candidate_id');
    }

    public function preferences(): HasOne
    {
        return $this->hasOne(CandidatePreference::class, 'candidate_id');
    }

    public function notificationSettings(): HasOne
    {
        return $this->hasOne(CandidateNotificationSetting::class, 'candidate_id');
    }

    public function privacySettings(): HasOne
    {
        return $this->hasOne(CandidatePrivacySetting::class, 'candidate_id');
    }

    protected function lookups(): array
    {
        return [
            'status' => ['status_id', Status::class, 'active', 'candidate_profile'],
            'approval_status' => ['approval_status_id', ApprovalStatus::class, 'under_review'],
        ];
    }

    public function approvalStatus(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ApprovalStatus::class, 'approval_status_id');
    }

    public function getLocationAttribute(): ?string
    {
        return $this->city?->name;
    }

    public function setLocationAttribute(?string $value): void
    {
        $name = trim((string) $value);
        if ($name === '') {
            $this->attributes['city_id'] = null;
            return;
        }

        $cityId = City::query()->where('name', $name)->value('id');
        if ($cityId !== null) {
            $this->attributes['city_id'] = (int) $cityId;
        }
    }

    public function recordStatus(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function city(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }
}
