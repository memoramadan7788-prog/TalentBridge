<?php

namespace App\Models;

class Currency extends LookupModel
{
    protected $table = 'currencies';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;
}
