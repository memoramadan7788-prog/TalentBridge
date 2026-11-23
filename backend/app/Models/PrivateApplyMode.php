<?php

namespace App\Models;

class PrivateApplyMode extends LookupModel
{
    protected $table = 'private_apply_modes';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;
}
