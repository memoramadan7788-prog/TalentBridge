<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SocialMediaPlatform extends Model
{
    protected $table = 'social_media_platforms';

    protected $fillable = [
        'name',
        'icon',
        'base_domain',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function candidateSocialLinks(): HasMany
    {
        return $this->hasMany(CandidateSocialLink::class, 'platform_id');
    }

    public function companySocialMedia(): HasMany
    {
        return $this->hasMany(CompanySocialMedia::class, 'platform_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Validate whether a full URL matches this platform's base_domain (if specified).
     */
    public function matchesDomain(string $url): bool
    {
        if (empty($this->base_domain)) {
            return true; // Any domain allowed (e.g. personal portfolio)
        }

        $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');
        $expected = strtolower($this->base_domain);

        return $host === $expected || str_ends_with($host, '.' . $expected);
    }
}
