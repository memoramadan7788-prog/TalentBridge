<?php

namespace App\Models;

class QuestionSource extends LookupModel
{
    protected $table = 'question_sources';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;
}
