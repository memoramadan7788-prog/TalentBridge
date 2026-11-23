<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\HasRecordVisibility;
use App\Traits\HasCompanyStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use \App\Models\Concerns\HasLookupAttributes;

    use Auditable, HasCompanyStatus, HasFactory, HasRecordVisibility, SoftDeletes;

    protected $table = 'companies';

    protected $guarded = [];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function reviewerAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'reviewed_by_admin_id');
    }

    public function userRoles(): HasMany
    {
        return $this->hasMany(UserRole::class, 'company_id');
    }

    public function socialLinks(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(SocialLink::class, 'linkable');
    }

    public function companyEmployees(): HasMany
    {
        return $this->hasMany(CompanyEmployee::class, 'company_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(CompanyDocument::class, 'company_id');
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class, 'company_id');
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class, 'company_id');
    }

    public function salaryRecords(): HasMany
    {
        return $this->hasMany(SalaryRecord::class, 'company_id');
    }

    protected function lookups(): array
    {
        return [
            'approval_status' => ['approval_status_id', ApprovalStatus::class, 'under_review'],
            'industry' => ['industry_id', Industry::class, null],
            'size' => ['company_size_id', CompanySize::class, null],
            'timezone' => ['timezone_id', Timezone::class, null],
        ];
    }

    public function approvalStatus(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ApprovalStatus::class, 'approval_status_id');
    }

    public function companyStatus(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CompanyStatus::class, 'company_status_id');
    }

    public function industry(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Industry::class, 'industry_id');
    }

    public function companySize(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CompanySize::class, 'company_size_id');
    }

    public function timezone(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Timezone::class, 'timezone_id');
    }

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Area::class, 'company_locations', 'company_id', 'area_id')
            ->withPivot('is_headquarters')
            ->withTimestamps();
    }

    public function headquarters(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(CompanyLocation::class, 'company_id')->where('is_headquarters', true);
    }

    public function benefits(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Benefit::class, 'company_benefits', 'company_id', 'benefit_id');
    }

    public function workspace(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(CompanyWorkspace::class, 'company_id');
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class, 'company_id');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(CompanyInvitation::class, 'company_id');
    }

    public function talentPool(): HasMany
    {
        return $this->hasMany(CompanyTalentPool::class, 'company_id');
    }

    public function offerLetterTemplates(): HasMany
    {
        return $this->hasMany(OfferLetterTemplate::class, 'company_id');
    }
}
