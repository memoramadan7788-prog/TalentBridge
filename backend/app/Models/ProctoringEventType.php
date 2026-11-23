<?php

namespace App\Models;

class ProctoringEventType extends LookupModel
{
    protected $table = 'proctoring_event_types';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;
}
