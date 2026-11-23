<?php

namespace App\Models;

class CompanyApprovalStatus extends LookupModel
{
    protected $table = 'company_approval_statuses';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;
}
