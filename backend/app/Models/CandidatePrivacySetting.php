<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidatePrivacySetting extends Model
{
    use \App\Models\Concerns\HasLookupAttributes;

    protected $guarded = [];

    protected $casts = [
        'allow_agency_recruiters' => 'boolean',
        'show_in_public_search' => 'boolean',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(CandidateProfile::class, 'candidate_id');
    }

    protected function lookups(): array
    {
        return [
            'visibility' => ['profile_visibility_level_id', ProfileVisibilityLevel::class, 'employers_only'],
        ];
    }

    public function profileVisibilityLevel(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProfileVisibilityLevel::class, 'profile_visibility_level_id');
    }
}
