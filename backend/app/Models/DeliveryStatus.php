<?php

namespace App\Models;

class DeliveryStatus extends LookupModel
{
    protected $table = 'delivery_statuses';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;
}
