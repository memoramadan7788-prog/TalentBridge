<?php

namespace App\Models;

class SalarySource extends LookupModel
{
    protected $table = 'salary_sources';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;
}
