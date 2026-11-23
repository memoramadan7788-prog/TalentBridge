<?php

namespace App\Models;

class TalentRelationship extends LookupModel
{
    protected $table = 'talent_relationships';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;
}
