<?php

namespace App\Models;

class SubmissionStatus extends LookupModel
{
    protected $table = 'submission_statuses';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;
}
