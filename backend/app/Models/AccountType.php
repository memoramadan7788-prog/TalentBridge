<?php

namespace App\Models;

class AccountType extends LookupModel
{
    protected $table = 'account_types';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;
}
