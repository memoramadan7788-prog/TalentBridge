<?php

namespace App\Traits;

use App\Models\Visibility;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasRecordVisibility
{
    protected static function bootHasRecordVisibility(): void
    {
        static::creating(function ($model): void {
            if ($model->visibility_id === null) {
                $model->visibility_id = 1;
            }
        });
    }

    public function visibilityLookup(): BelongsTo
    {
        return $this->belongsTo(Visibility::class, 'visibility_id');
    }
}
