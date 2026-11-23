<?php

namespace App\Models;

class ContactMessageStatus extends LookupModel
{
    protected $table = 'contact_message_statuses';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;
}
