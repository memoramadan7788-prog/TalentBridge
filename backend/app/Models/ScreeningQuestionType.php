<?php

namespace App\Models;

class ScreeningQuestionType extends LookupModel
{
    protected $table = 'screening_question_types';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;
}
