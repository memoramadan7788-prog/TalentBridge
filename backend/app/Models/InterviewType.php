<?php

namespace App\Models;

class InterviewType extends LookupModel
{
    protected $table = 'interview_types';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;
}
