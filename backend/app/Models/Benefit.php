<?php

namespace App\Models;

class Benefit extends LookupModel
{
    protected $table = 'benefits';
    protected static string $keyColumn = 'name';
    protected static bool $autoCreate = true;
}
