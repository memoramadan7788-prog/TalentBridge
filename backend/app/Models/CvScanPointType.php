<?php

namespace App\Models;

/** strength / weakness */
class CvScanPointType extends LookupModel
{
    protected $table = 'cv_scan_point_types';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;
}
