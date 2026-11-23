<?php

namespace App\Models;

class NotificationChannel extends LookupModel
{
    protected $table = 'notification_channels';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;
}
