<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use HasFactory, SoftDeletes, Auditable;


    protected $table = 'countries';
    protected $guarded = [];

    protected $attributes = [
        'status_id' => Status::ACCOUNT_ACTIVE,
    ];

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'country_id');
    }

    public function cities(): HasMany
    {
        return $this->hasMany(City::class, 'country_id');
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class, 'country_id');
    }
}
