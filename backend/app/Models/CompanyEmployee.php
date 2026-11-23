<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanyEmployee extends Model
{
    use \App\Models\Concerns\HasLookupAttributes;

    use SoftDeletes, Auditable;


    protected $table = 'company_employees';
    protected $guarded = [];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'requested_at' => 'datetime',
        'joined_at' => 'datetime',
        'left_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** roles live in user_roles (single source of truth), scoped to this employee's company */
    public function roles(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id', 'user_id')
            ->wherePivot('company_id', $this->company_id);
    }

    public function role(): \Illuminate\Database\Eloquent\Relations\HasOneThrough
    {
        return $this->hasOneThrough(Role::class, UserRole::class, 'user_id', 'id', 'user_id', 'role_id')
            ->where('user_roles.company_id', $this->company_id);
    }

    public function requestedDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'requested_department_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function jobNominations(): HasMany
    {
        return $this->hasMany(JobNomination::class, 'employee_id');
    }

    /**
     * Return the employee's retained work period.
     *
     * The record remains after removal, so the period is calculated from
     * joined_at to left_at (or now() for an active employee).
     */
    public function employmentPeriod(): ?array
    {
        if (! $this->joined_at) {
            return null;
        }

        $end = $this->left_at ?? now();

        return [
            'days' => (int) $this->joined_at->diffInDays($end),
            'is_current' => $this->left_at === null,
        ];
    }

    protected function lookups(): array
    {
        return [
            'status' => ['status_id', Status::class, 'active', 'company_employee'],
            'approval_status' => ['approval_status_id', ApprovalStatus::class, 'under_review'],
            'requested_job_title' => ['requested_job_title_id', JobTitle::class, null],
        ];
    }

    public function approvalStatus(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ApprovalStatus::class, 'approval_status_id');
    }

    public function companyEmployeeStatus(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function requestedJobTitle(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(JobTitle::class, 'requested_job_title_id');
    }
}
