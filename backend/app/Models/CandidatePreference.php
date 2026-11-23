<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidatePreference extends Model
{
    use \App\Models\Concerns\HasLookupAttributes;

    protected $guarded = [];

    protected $casts = [
        'min_salary' => 'decimal:2',
        'open_to_relocation' => 'boolean',
        'include_agency' => 'boolean',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(CandidateProfile::class, 'candidate_id');
    }

    protected function lookups(): array
    {
        return [
            'employment_type' => ['employment_type_id', EmploymentType::class, null],
            'work_model' => ['work_model_id', WorkMode::class, null],
            'location' => ['city_id', City::class, null],
        ];
    }

    public function employmentType(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(EmploymentType::class, 'employment_type_id');
    }

    public function workMode(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(WorkMode::class, 'work_model_id');
    }

    public function city(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function desiredTitles(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(JobTitle::class, 'candidate_preference_job_titles', 'candidate_preference_id', 'job_title_id');
    }
}
