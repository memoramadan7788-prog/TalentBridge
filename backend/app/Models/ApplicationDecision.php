<?php

namespace App\Models;

class ApplicationDecision extends LookupModel
{
    protected $table = 'application_decisions';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;
}
