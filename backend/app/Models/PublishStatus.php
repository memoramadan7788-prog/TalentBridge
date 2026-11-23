<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PublishStatus extends Model
{
    public const DRAFT = 1;
    public const PUBLISHED = 2;
    public const CLOSED = 3;
    public const ARCHIVED = 4;

    protected $table = 'publish_statuses';
    protected $guarded = [];
    public $incrementing = false;
    protected $keyType = 'int';

    public static function idFor(string|int|null $value): int
    {
        if (is_numeric($value)) {
            return (int) $value;
        }

        return (int) static::where('name', $value ?: 'draft')->value('id');
    }

    public function jobs(): HasMany { return $this->hasMany(Job::class, 'publish_status_id'); }
    public function exams(): HasMany { return $this->hasMany(Exam::class, 'publish_status_id'); }
    public function questions(): HasMany { return $this->hasMany(Question::class, 'publish_status_id'); }
}
