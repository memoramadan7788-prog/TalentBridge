<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProctoringEvent extends Model
{
    use \App\Models\Concerns\HasLookupAttributes;

    use SoftDeletes, Auditable;


    protected $table = 'proctoring_events';
    protected $guarded = [];

    protected $casts = [
        'occurred_at' => 'datetime',
    ];

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(ExamAttempt::class, 'attempt_id');
    }

    public function proctoringReviews(): HasMany
    {
        return $this->hasMany(ProctoringReview::class, 'event_id');
    }

    protected function lookups(): array
    {
        return [
            'event_type' => ['proctoring_event_type_id', ProctoringEventType::class, null],
        ];
    }

    public function proctoringEventType(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProctoringEventType::class, 'proctoring_event_type_id');
    }
}
