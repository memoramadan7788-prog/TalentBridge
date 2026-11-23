<?php

namespace App\Models;

class ProctoringDecision extends LookupModel
{
    protected $table = 'proctoring_decisions';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;
}
