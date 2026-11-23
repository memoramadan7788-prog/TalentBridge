<?php

namespace App\Models;

class DocumentType extends LookupModel
{
    protected $table = 'document_types';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;
}
