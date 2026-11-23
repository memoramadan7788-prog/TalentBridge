<?php

namespace App\Models;

class AvailabilityStatus extends LookupModel
{
    protected $table = 'availability_statuses';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;
}
