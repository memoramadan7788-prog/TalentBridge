<?php

namespace App\Models;

class SyncStatus extends LookupModel
{
    protected $table = 'sync_statuses';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;
}
