<?php

namespace App\Traits;

use App\Models\CompanyStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasCompanyStatus
{
    public function companyStatus(): BelongsTo
    {
        return $this->belongsTo(CompanyStatus::class, 'company_status_id');
    }

    /**
     * Keep the existing API attribute name while storing the workflow FK.
     */
    public function getStatusAttribute($value): ?string
    {
        return $this->companyStatus?->name;
    }

    public function setStatusAttribute(string|int|null $value): void
    {
        $this->attributes['company_status_id'] = $value === null
            ? null
            : CompanyStatus::idFor($value);
    }
}
