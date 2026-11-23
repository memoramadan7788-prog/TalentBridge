<?php

namespace App\Models;

class GradingStatus extends LookupModel
{
    protected $table = 'grading_statuses';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;
}
