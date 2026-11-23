<?php

namespace App\Models;

class JobStatus extends LookupModel
{
    protected $table = 'job_statuses';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;
}
