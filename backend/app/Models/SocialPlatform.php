<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/** The shared platform list (code, name, icon) used by every social link. */
class SocialPlatform extends LookupModel
{
    protected $table = 'social_platforms';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;

    public function socialLinks(): HasMany
    {
        return $this->hasMany(SocialLink::class, 'social_platform_id');
    }
}
