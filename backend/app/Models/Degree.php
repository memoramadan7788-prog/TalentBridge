<?php

namespace App\Models;

class Degree extends LookupModel
{
    protected $table = 'degrees';
    protected static string $keyColumn = 'name';
    protected static bool $autoCreate = false;
}
