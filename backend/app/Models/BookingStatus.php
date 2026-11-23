<?php

namespace App\Models;

class BookingStatus extends LookupModel
{
    protected $table = 'booking_statuses';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;
}
