<?php

namespace App\Models;

class GradingMethod extends LookupModel
{
    protected $table = 'grading_methods';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;
}
