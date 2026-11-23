<?php

namespace App\Traits;

use App\Models\PublishStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasPublishStatus
{
    public function publishStatus(): BelongsTo
    {
        return $this->belongsTo(PublishStatus::class, 'publish_status_id');
    }

    /**
     * Keep the existing API attribute name while storing only the FK.
     */
    public function getStatusAttribute($value): ?string
    {
        return $this->publishStatus?->name;
    }

    public function setStatusAttribute(string|int|null $value): void
    {
        $this->attributes['publish_status_id'] = $value === null
            ? null
            : PublishStatus::idFor($value);
    }
}
