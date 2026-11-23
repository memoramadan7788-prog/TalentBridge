<?php

namespace App\Models;

class QuestionStatus extends LookupModel
{
    protected $table = 'question_statuses';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;
}
