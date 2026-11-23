<?php

namespace App\Models;

class ReviewDecision extends LookupModel
{
    protected $table = 'review_decisions';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;
}
