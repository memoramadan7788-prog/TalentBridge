<?php

namespace App\Models;

class CompanySize extends LookupModel
{
    protected $table = 'company_sizes';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;
}
