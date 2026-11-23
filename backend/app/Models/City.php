<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class City extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $table = 'cities';
    protected $guarded = [];

    public static function idFor(string $name): int
    {
        $id = static::query()->where('name', $name)->value('id');

        if (! $id) {
            throw new \InvalidArgumentException("Unknown city: {$name}");
        }

        return (int) $id;
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function areas(): HasMany
    {
        return $this->hasMany(Area::class, 'city_id');
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class, 'city_id');
    }
}
