<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CvScanPoint extends Model
{
    use \App\Models\Concerns\HasLookupAttributes;

    protected $table = 'cv_scan_points';
    protected $guarded = [];

    public function scan(): BelongsTo
    {
        return $this->belongsTo(CvScan::class, 'cv_scan_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(CvScanPointType::class, 'cv_scan_point_type_id');
    }

    public function pointType(): BelongsTo
    {
        return $this->type();
    }

    /** $point->point_type = 'strength' */
    protected function lookups(): array
    {
        return [
            'point_type' => ['cv_scan_point_type_id', CvScanPointType::class, null],
        ];
    }
}
