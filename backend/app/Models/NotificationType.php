<?php

namespace App\Models;

class NotificationType extends LookupModel
{
    protected $table = 'notification_types';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = true;
}
