<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class OfferStatus extends LookupModel
{
    public const SENT = 1;
    public const ACCEPTED = 2;
    public const REJECTED = 3;
    public const EXPIRED = 4;

    protected $table = 'offer_statuses';

    protected static string $keyColumn = 'name';

    protected static bool $autoCreate = false;

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class, 'offer_status_id');
    }
}