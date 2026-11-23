<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** One social link (linkedin, github, ...) that belongs to a Company OR a CandidateProfile. */
class SocialLink extends Model
{
    use \App\Models\Concerns\HasLookupAttributes;
    use SoftDeletes, Auditable;

    protected $table = 'social_links';
    protected $guarded = [];

    /** the owner: Company or CandidateProfile */
    public function linkable(): MorphTo
    {
        return $this->morphTo();
    }

    public function socialPlatform(): BelongsTo
    {
        return $this->belongsTo(SocialPlatform::class, 'social_platform_id');
    }

    /** $link->platform = 'linkedin' / $link->platform reads the code back */
    protected function lookups(): array
    {
        return [
            'platform' => ['social_platform_id', SocialPlatform::class, null],
        ];
    }
}
