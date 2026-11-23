<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanyTalentPool extends Model
{
    use \App\Models\Concerns\HasLookupAttributes;

    protected $table = 'company_talent_pool';
    protected $guarded = [];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(CandidateProfile::class, 'candidate_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'company_talent_pool_tag', 'company_talent_pool_id', 'tag_id');
    }

    protected $casts = ['consent_at' => 'datetime'];

    protected function lookups(): array
    {
        return [
            'source' => ['application_source_id', ApplicationSource::class, null],
            'relationship' => ['talent_relationship_id', TalentRelationship::class, null],
        ];
    }

    public function applicationSource(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ApplicationSource::class, 'application_source_id');
    }

    public function talentRelationship(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(TalentRelationship::class, 'talent_relationship_id');
    }
}
