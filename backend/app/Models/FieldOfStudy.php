<?php

namespace App\Models;

class FieldOfStudy extends LookupModel
{
    protected $table = 'fields_of_study';
    protected static string $keyColumn = 'name';
    protected static bool $autoCreate = false;
}
