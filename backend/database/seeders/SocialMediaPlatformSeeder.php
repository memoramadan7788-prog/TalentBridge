<?php

namespace Database\Seeders;

use App\Models\SocialMediaPlatform;
use Illuminate\Database\Seeder;

class SocialMediaPlatformSeeder extends Seeder
{
    public function run(): void
    {
        $platforms = [
            [
                'name'        => 'LinkedIn',
                'icon'        => 'linkedin',
                'base_domain' => 'linkedin.com',
                'is_active'   => true,
            ],
            [
                'name'        => 'GitHub',
                'icon'        => 'github',
                'base_domain' => 'github.com',
                'is_active'   => true,
            ],
            [
                'name'        => 'Twitter / X',
                'icon'        => 'twitter',
                'base_domain' => 'x.com',
                'is_active'   => true,
            ],
            [
                'name'        => 'Facebook',
                'icon'        => 'facebook',
                'base_domain' => 'facebook.com',
                'is_active'   => true,
            ],
            [
                'name'        => 'Instagram',
                'icon'        => 'instagram',
                'base_domain' => 'instagram.com',
                'is_active'   => true,
            ],
            [
                'name'        => 'YouTube',
                'icon'        => 'youtube',
                'base_domain' => 'youtube.com',
                'is_active'   => true,
            ],
            [
                'name'        => 'Behance',
                'icon'        => 'behance',
                'base_domain' => 'behance.net',
                'is_active'   => true,
            ],
            [
                'name'        => 'Dribbble',
                'icon'        => 'dribbble',
                'base_domain' => 'dribbble.com',
                'is_active'   => true,
            ],
            [
                'name'        => 'Portfolio / Website',
                'icon'        => 'globe',
                'base_domain' => null, // allows any valid domain
                'is_active'   => true,
            ],
        ];

        foreach ($platforms as $platform) {
            SocialMediaPlatform::updateOrCreate(
                ['name' => $platform['name']],
                $platform
            );
        }
    }
}
