<?php

namespace App\Models;

class HiringTeamRole extends LookupModel
{
    protected $table = 'hiring_team_roles';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;
}
