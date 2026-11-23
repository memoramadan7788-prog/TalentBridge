<?php

namespace App\Models;

class SkillType extends LookupModel
{
    protected $table = 'skill_types';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;
}
