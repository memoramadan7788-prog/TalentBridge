<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Skill extends Model
{
    use \App\Models\Concerns\HasLookupAttributes;

    use HasFactory, SoftDeletes, Auditable;


    protected $table = 'skills';
    protected $guarded = [];

    protected $casts = [
        'accepted' => 'boolean',
    ];

    public function candidates(): BelongsToMany
    {
        return $this->belongsToMany(CandidateProfile::class, 'candidate_skills', 'skill_id', 'candidate_id')->withPivot('level');
    }

    public function jobs(): BelongsToMany
    {
        return $this->belongsToMany(Job::class, 'job_skills', 'skill_id', 'job_id')->withPivot('is_required');
    }

    protected function lookups(): array
    {
        return [
            'type' => ['skill_type_id', SkillType::class, 'soft'],
        ];
    }

    public function skillType(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(SkillType::class, 'skill_type_id');
    }
}
