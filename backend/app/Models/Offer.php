<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\HasRecordVisibility;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\HasLookupAttributes;

class Offer extends Model
{
    use HasLookupAttributes;
    use SoftDeletes, Auditable, HasRecordVisibility;

    protected $table = 'offers';

    protected $guarded = [];

    protected $casts = [
        'start_date' => 'date',
        'sent_at' => 'datetime',
        'responded_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected function lookups(): array
    {
        return [
            'currency' => ['currency_id', Currency::class, 'EGP'],
            'salary_period' => ['salary_period_id', SalaryPeriod::class, 'month'],
            'status' => ['offer_status_id', OfferStatus::class, 'sent'],
            'employment_type' => ['employment_type_id', EmploymentType::class, null],
            'work_model' => ['work_mode_id', WorkMode::class, null],
            'approval_status' => ['approval_status_id', Status::class, 'not_required', 'offer_approval'],
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class, 'application_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function salaryPeriod(): BelongsTo
    {
        return $this->belongsTo(SalaryPeriod::class, 'salary_period_id');
    }

    public function offerStatus(): BelongsTo
    {
        return $this->belongsTo(OfferStatus::class, 'offer_status_id');
    }

    public function recordStatus(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function employmentType(): BelongsTo
    {
        return $this->belongsTo(EmploymentType::class, 'employment_type_id');
    }

    public function workMode(): BelongsTo
    {
        return $this->belongsTo(WorkMode::class, 'work_mode_id');
    }

    public function offerApprovalStatus(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'approval_status_id');
    }
}