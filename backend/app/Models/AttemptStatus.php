<?php

namespace App\Models;

class AttemptStatus extends LookupModel
{
    protected $table = 'attempt_statuses';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;
}
