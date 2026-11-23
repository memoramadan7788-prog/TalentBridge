<?php

namespace App\Models;

class CalendarProvider extends LookupModel
{
    protected $table = 'calendar_providers';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;
}
