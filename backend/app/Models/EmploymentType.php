<?php

namespace App\Models;

class EmploymentType extends LookupModel
{
    protected $table = 'employment_types';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;
}
