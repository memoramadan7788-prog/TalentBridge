<?php

namespace App\Models;

class InterviewStatus extends LookupModel
{
    protected $table = 'interview_statuses';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;
}
