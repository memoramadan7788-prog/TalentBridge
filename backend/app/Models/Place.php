<?php

namespace App\Models;

class Place extends LookupModel
{
    protected $table = 'places';
    protected static string $keyColumn = 'name';
    protected static bool $autoCreate = false;
}
