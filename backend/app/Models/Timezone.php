<?php

namespace App\Models;

class Timezone extends LookupModel
{
    protected $table = 'timezones';
    protected static string $keyColumn = 'name';
    protected static bool $autoCreate = true;
}
