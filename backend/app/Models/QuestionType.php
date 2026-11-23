<?php

namespace App\Models;

class QuestionType extends LookupModel
{
    protected $table = 'question_types';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;
}
