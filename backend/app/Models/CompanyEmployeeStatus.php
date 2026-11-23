<?php

namespace App\Models;

class CompanyEmployeeStatus extends LookupModel
{
    protected $table = 'company_employee_statuses';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;
}
