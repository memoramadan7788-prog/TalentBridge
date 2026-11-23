<?php

namespace App\Models;

class ExamStatus extends LookupModel
{
    protected $table = 'exam_statuses';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;
}
