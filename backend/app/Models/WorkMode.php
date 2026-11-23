<?php

namespace App\Models;

class WorkMode extends LookupModel
{
    protected $table = 'work_modes';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;
}
