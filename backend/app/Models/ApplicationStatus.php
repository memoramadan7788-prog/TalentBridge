<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApplicationStatus extends Model
{
    public const SUBMITTED = 1;
    public const SCREENING = 2;
    public const SHORTLISTED = 3;
    public const INTERVIEWING = 4;
    public const OFFERED = 5;
    public const HIRED = 6;
    public const REJECTED = 7;
    public const WITHDRAWN = 8;

    protected $table = 'application_statuses';
    protected $guarded = [];
    public $incrementing = false;
    protected $keyType = 'int';

    public static function idFor(string|int|null $value): int
    {
        if (is_numeric($value)) {
            return (int) $value;
        }

        return match (strtolower(trim((string) ($value ?: 'submitted')))) {
            'pending', 'applied', 'submitted' => self::SUBMITTED,
            'under_review', 'screening' => self::SCREENING,
            'shortlisted' => self::SHORTLISTED,
            'scheduling_meeting', 'interview', 'interviewing' => self::INTERVIEWING,
            'offer', 'offered' => self::OFFERED,
            'hired' => self::HIRED,
            'rejected' => self::REJECTED,
            'withdrawn' => self::WITHDRAWN,
            default => self::SUBMITTED,
        };
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'application_status_id');

    }
}
