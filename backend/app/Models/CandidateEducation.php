<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\HasRecordVisibility;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateEducation extends Model
{
    use \App\Models\Concerns\HasLookupAttributes;

    use SoftDeletes, Auditable, HasRecordVisibility;


    protected $table = 'candidate_education';
    protected $guarded = [];
    protected $attributes = ['visibility_id' => 1];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(CandidateProfile::class, 'candidate_id');
    }

    protected function lookups(): array
    {
        return [
            'institution' => ['institution_id', Institution::class, null],
            'degree' => ['degree_id', Degree::class, null],
            'field_of_study' => ['field_of_study_id', FieldOfStudy::class, null],
            'place' => ['place_id', Place::class, null],
            'status' => ['status_id', Status::class, 'active', 'candidate_education'],
        ];
    }

    public function institutionOption(): BelongsTo
    {
        return $this->belongsTo(Institution::class, 'institution_id');
    }

    public function degreeOption(): BelongsTo
    {
        return $this->belongsTo(Degree::class, 'degree_id');
    }

    public function fieldOfStudyOption(): BelongsTo
    {
        return $this->belongsTo(FieldOfStudy::class, 'field_of_study_id');
    }

    public function placeOption(): BelongsTo
    {
        return $this->belongsTo(Place::class, 'place_id');
    }

    public function recordStatus(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }
}
