<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanyStatus extends Model
{
    public const PENDING = 1;
    public const APPROVED = 2;
    public const REJECTED = 3;
    public const SUSPENDED = 4;

    protected $table = 'company_statuses';
    protected $guarded = [];
    public $incrementing = false;
    protected $keyType = 'int';

    public static function idFor(string|int|null $value): int
    {
        if (is_numeric($value)) {
            return (int) $value;
        }

        return (int) static::where('name', $value ?: 'pending')->value('id');
    }

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class, 'company_status_id');
    }
}
