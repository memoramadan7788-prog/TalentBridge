<?php

namespace App\Models;

class Tag extends LookupModel
{
    protected $table = 'tags';
    protected static string $keyColumn = 'name';
    protected static bool $autoCreate = true;
}
