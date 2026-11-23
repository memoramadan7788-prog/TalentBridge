<?php

namespace App\Models;

class EngagementStatus extends LookupModel
{
    protected $table = 'engagement_statuses';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;
}
