<?php

namespace App\Traits;

use App\Models\Status;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasRecordStatus
{
    protected static function bootHasRecordStatus(): void
    {
        static::creating(function ($model): void {
            if ($model->status_id === null) {
                $model->status_id = 1;
            }
        });
    }

    public function recordStatus(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }
}
