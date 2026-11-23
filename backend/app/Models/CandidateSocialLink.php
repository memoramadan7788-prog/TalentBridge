<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateSocialLink extends Model
{
    use \App\Models\Concerns\HasLookupAttributes;

    use SoftDeletes, Auditable;


    protected $table = 'candidate_social_links';
    protected $guarded = [];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(CandidateProfile::class, 'candidate_id');
    }

    protected function lookups(): array
    {
        return [
            'platform' => ['social_platform_id', SocialPlatform::class, null],
        ];
    }

    public function socialPlatform(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(SocialPlatform::class, 'social_platform_id');
    }
}
