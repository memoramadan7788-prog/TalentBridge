<?php

namespace App\Models;

class SkillLevel extends LookupModel
{
    protected $table = 'skill_levels';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;
}
