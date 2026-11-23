<?php

namespace App\Models;

class Industry extends LookupModel
{
    protected $table = 'industries';
    protected static string $keyColumn = 'name';
    protected static bool $autoCreate = true;
}
