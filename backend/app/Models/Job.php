<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\PublishStatus;
use App\Models\CompanyStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Traits\Auditable;
use App\Traits\HasRecordVisibility;
use App\Traits\HasPublishStatus;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Job extends Model
{
    use \App\Models\Concerns\HasLookupAttributes;

    use HasFactory, SoftDeletes, Auditable, HasRecordVisibility;

    protected $table = 'jobs';
    protected $guarded = [];
    protected $attributes = [
        'visibility_id' => 1,
        'publish_status_id' => 1,
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'published_at' => 'datetime',
        'closed_at' => 'datetime',
        'allow_agency_submissions' => 'boolean',
        'publish_to_search' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /** the subcategory, reached through the job title (the only place a job's category is stored) */
    public function subcategory(): \Illuminate\Database\Eloquent\Relations\HasOneThrough
    {
        return $this->hasOneThrough(JobCategory::class, JobTitle::class, 'id', 'id', 'job_title_id', 'job_category_id');
    }

    /** the top-level category: $job->category */
    public function getCategoryAttribute(): ?JobCategory
    {
        $jobTitle = $this->relationLoaded('jobTitle')
            ? $this->getRelation('jobTitle')
            : $this->jobTitle;

        return $jobTitle?->category?->parent;
    }

    public function jobTitle(): BelongsTo
    {
        return $this->belongsTo(JobTitle::class, 'job_title_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function hiringTeam(): HasMany
    {
        return $this->hasMany(JobHiringTeamMember::class, 'job_id');
    }

    public function requisitionOwner(): \Illuminate\Database\Eloquent\Relations\HasOneThrough
    {
        return $this->teamMemberWithRole('requisition_owner');
    }

    public function hiringManager(): \Illuminate\Database\Eloquent\Relations\HasOneThrough
    {
        return $this->teamMemberWithRole('hiring_manager');
    }

    private function teamMemberWithRole(string $role): \Illuminate\Database\Eloquent\Relations\HasOneThrough
    {
        return $this->hasOneThrough(User::class, JobHiringTeamMember::class, 'job_id', 'id', 'id', 'user_id')
            ->where('job_hiring_team.hiring_team_role_id', HiringTeamRole::idFor($role));
    }

    public function benefits(): BelongsToMany
    {
        return $this->belongsToMany(Benefit::class, 'job_benefits', 'job_id', 'benefit_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function jobInterviewRounds(): HasMany
    {
        return $this->hasMany(JobInterviewRound::class, 'job_id');
    }

    public function jobNominations(): HasMany
    {
        return $this->hasMany(JobNomination::class, 'job_id');
    }

    public function jobExams(): HasMany
    {
        return $this->hasMany(JobExam::class, 'job_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'job_id');
    }

    public function jobMatches(): HasMany
    {
        return $this->hasMany(JobMatch::class, 'job_id');
    }

    public function screeningQuestions(): HasMany
    {
        return $this->hasMany(JobScreeningQuestion::class, 'job_id')->orderBy('sort_order');
    }

    public function applicationDrafts(): HasMany
    {
        return $this->hasMany(CandidateApplicationDraft::class, 'job_id');
    }

    public function salaryRecommendations(): HasMany
    {
        return $this->hasMany(SalaryRecommendation::class, 'job_id');
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'job_skills', 'job_id', 'skill_id')->withPivot('is_required');
    }

    public function savedByCandidates(): BelongsToMany
    {
        return $this->belongsToMany(CandidateProfile::class, 'candidate_saved_jobs', 'job_id', 'candidate_id')
            ->withTimestamps();
    }

    public const SORTABLE = [
        'published_at' => 'jobs.published_at',
        'title'        => 'jobs.title',
        'salary_min'   => 'jobs.salary_min',
        'deadline'     => 'jobs.deadline',
    ];

    public const PUBLIC_COLUMNS = [
        'jobs.id', 'jobs.company_id', 'jobs.job_title_id', 'jobs.title',
        'jobs.description', 'jobs.city_id', 'jobs.work_mode_id',
        'jobs.employment_type_id', 'jobs.salary_min', 'jobs.salary_max',
        'jobs.currency_id', 'jobs.salary_period_id', 'jobs.experience_years',
        'jobs.department_id', 'jobs.openings_count', 'jobs.deadline',
        'jobs.published_at', 'jobs.updated_at', 'jobs.requisition_code',
        'jobs.hiring_model_id', 'jobs.status_id', 'jobs.visibility_id',
    ];

    /** legacy 'public'|'private' <-> visibility_id (the enum column no longer exists) */
    public function setVisibilityAttribute(?string $value): void
    {
        if ($value !== null) {
            $this->attributes['visibility_id'] = $value === 'private' ? 2 : 1;
        }
    }

    public function getVisibilityAttribute(): string
    {
        return ((int) ($this->attributes['visibility_id'] ?? 1)) === 2 ? 'private' : 'public';
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query
            ->whereLookup('status', 'published')
            ->where('jobs.publish_status_id', PublishStatus::PUBLISHED)
            ->where('jobs.visibility_id', 1)
            ->whereNull('jobs.closed_at')
            ->where(function (Builder $q) {
                $q->whereNull('jobs.deadline')
                  ->orWhere('jobs.deadline', '>', now());
            })
            ->whereHas('company', fn (Builder $c) => $c->whereLookup('approval_status', 'accepted'));
    }

    public function scopeAvailableInternal(Builder $query, int $companyId, ?int $userId = null): Builder
    {
        return $query
            ->where('jobs.company_id', $companyId)
            ->whereLookup('status', 'published')
            ->where('jobs.publish_status_id', PublishStatus::PUBLISHED)
            ->where('jobs.visibility_id', 2)
            ->whereNull('jobs.closed_at')
            ->where(function (Builder $q) {
                $q->whereNull('jobs.deadline')
                    ->orWhere('jobs.deadline', '>', now());
            })
            ->where(function (Builder $q) use ($userId, $companyId) {
                $q->whereLookup('private_apply_mode', 'all_employees')
                    ->orWhereNull('jobs.private_apply_mode_id');

                if ($userId) {
                    $q->orWhereHas('jobNominations', function (Builder $nomination) use ($userId, $companyId) {
                        $nomination
                            ->whereHas('employee', fn (Builder $employee) => $employee
                                ->where('user_id', $userId)
                                ->where('company_id', $companyId)
                                ->whereLookup('approval_status', 'accepted'));
                    });
                }
            })
            ->whereHas('company', fn (Builder $c) => $c->whereLookup('approval_status', 'accepted'));
    }

    public function scopeFilter(Builder $query, array $f): Builder
    {
        return $query
            ->when($f['q'] ?? null, function (Builder $q, string $term) {
                $like = self::likeTerm($term);
                $q->where(function (Builder $w) use ($like) {
                    $w->where('jobs.title', 'like', $like)
                      ->orWhere('jobs.description', 'like', $like)
                      ->orWhereHas('company', fn (Builder $c) => $c->where('name', 'like', $like));
                });
            })
            ->when($f['title'] ?? null, fn (Builder $q, string $v) => $q->where('jobs.title', 'like', self::likeTerm($v)))
            ->when($f['company_name'] ?? null, fn (Builder $q, string $v) => $q->whereHas(
                'company', fn (Builder $c) => $c->where('name', 'like', self::likeTerm($v))
            ))
            ->when($f['location'] ?? null, fn (Builder $q, string $v) => $q->whereHas('city', fn (Builder $c) => $c->where('name', 'like', self::likeTerm($v))))
            ->when($f['employment_type'] ?? null, fn (Builder $q, array $v) => $q->whereLookup('employment_type', $v))
            ->when($f['work_mode'] ?? null, fn (Builder $q, array $v) => $q->whereLookup('work_mode', $v))
            ->when($f['company_id'] ?? null, fn (Builder $q, $v) => $q->where('jobs.company_id', $v))
            ->when($f['category_id'] ?? null, fn (Builder $q, $v) => $q->whereHas('jobTitle.category', fn (Builder $c) => $c->where(
                fn (Builder $w) => $w->where('job_categories.id', $v)->orWhere('job_categories.parent_id', $v)
            )))
            ->when($f['job_title_id'] ?? null, fn (Builder $q, $v) => $q->where('jobs.job_title_id', $v))
            ->when(isset($f['status_id']), fn (Builder $q) => $q->where('jobs.status_id', $f['status_id']))
            ->when(isset($f['visibility_id']), fn (Builder $q) => $q->where('jobs.visibility_id', $f['visibility_id']))
            ->when($f['skills'] ?? null, fn (Builder $q, array $ids) => $q->whereHas(
                'skills', fn (Builder $s) => $s->whereIn('skills.id', $ids)
            ))
            ->when(isset($f['max_experience']), fn (Builder $q) => $q->where(function (Builder $w) use ($f) {
                $w->whereNull('jobs.experience_years')
                  ->orWhere('jobs.experience_years', '<=', $f['max_experience']);
            }))
            ->when(isset($f['salary_min']), fn (Builder $q) => $q->where('jobs.salary_max', '>=', $f['salary_min']))
            ->when(isset($f['salary_max']), fn (Builder $q) => $q->where('jobs.salary_min', '<=', $f['salary_max']))
            ->when($f['published_from'] ?? null, fn (Builder $q, string $v) => $q->where('jobs.published_at', '>=', Carbon::parse($v)->startOfDay()))
            ->when($f['published_to'] ?? null, fn (Builder $q, string $v) => $q->where('jobs.published_at', '<=', Carbon::parse($v)->endOfDay()));
    }

    private static function likeTerm(string $term): string
    {
        return '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term) . '%';
    }

    protected function lookups(): array
    {
        return [
            'status' => ['status_id', Status::class, 'draft', 'job'],
            'work_mode' => ['work_mode_id', WorkMode::class, 'onsite'],
            'employment_type' => ['employment_type_id', EmploymentType::class, 'full_time'],
            'salary_period' => ['salary_period_id', SalaryPeriod::class, 'month'],
            'currency' => ['currency_id', Currency::class, 'EGP'],
            'private_apply_mode' => ['private_apply_mode_id', PrivateApplyMode::class, null],
            'hiring_model' => ['hiring_model_id', HiringModel::class, 'direct'],
        ];
    }

    public function jobStatus(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function workMode(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(WorkMode::class, 'work_mode_id');
    }

    public function employmentType(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(EmploymentType::class, 'employment_type_id');
    }

    public function salaryPeriod(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(SalaryPeriod::class, 'salary_period_id');
    }

    public function currency(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function privateApplyMode(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PrivateApplyMode::class, 'private_apply_mode_id');
    }

    public function hiringModel(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(HiringModel::class, 'hiring_model_id');
    }

}
