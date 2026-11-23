<?php

namespace App\Models;

class SalaryPeriod extends LookupModel
{
    protected $table = 'salary_periods';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;
}
