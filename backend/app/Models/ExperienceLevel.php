<?php

namespace App\Models;

class ExperienceLevel extends LookupModel
{
    protected $table = 'experience_levels';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;
}
