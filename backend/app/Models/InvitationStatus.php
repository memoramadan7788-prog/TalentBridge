<?php

namespace App\Models;

class InvitationStatus extends LookupModel
{
    protected $table = 'invitation_statuses';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;
}
