<?php

namespace App\Models;

class HiringModel extends LookupModel
{
    protected $table = 'hiring_models';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;
}
