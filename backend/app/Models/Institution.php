<?php

namespace App\Models;

class Institution extends LookupModel
{
    protected $table = 'institutions';
    protected static string $keyColumn = 'name';
    protected static bool $autoCreate = false;
}
