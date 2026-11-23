<?php

namespace App\Models;

class CvRunStatus extends LookupModel
{
    protected $table = 'cv_run_statuses';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;
}
