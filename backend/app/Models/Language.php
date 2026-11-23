<?php

namespace App\Models;

class Language extends LookupModel
{
    protected $table = 'languages';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;
}
