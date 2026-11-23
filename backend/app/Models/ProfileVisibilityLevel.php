<?php

namespace App\Models;

class ProfileVisibilityLevel extends LookupModel
{
    protected $table = 'profile_visibility_levels';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;
}
