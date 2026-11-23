<?php

namespace App\Models;

class OfferApprovalStatus extends LookupModel
{
    protected $table = 'offer_approval_statuses';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;
}
