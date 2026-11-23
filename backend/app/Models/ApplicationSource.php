<?php

namespace App\Models;

class ApplicationSource extends LookupModel
{
    protected $table = 'application_sources';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;
}
