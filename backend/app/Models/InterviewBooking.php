<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InterviewBooking extends Model
{
    use \App\Models\Concerns\HasLookupAttributes;

    use HasFactory, SoftDeletes, Auditable;

    /**
     * The table associated with the model (Recruitment Platform ERD Page 4).
     */
    protected $table = 'interview_bookings';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'interview_id',
        'availability_id',
        'active_availability_id',
        'booking_status',
        'status_id',
        'cancelled_by',
        'cancelled_at',
        'UID',
        'LUID',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cancelled_at' => 'datetime',
        ];
    }

    /**
     * Associated interview.
     */
    public function interview(): BelongsTo
    {
        return $this->belongsTo(Interview::class, 'interview_id');
    }

    /**
     * Assigned interviewer user.
     */
    public function interviewer(): \Illuminate\Database\Eloquent\Relations\HasOneThrough
    {
        return $this->hasOneThrough(User::class, InterviewerAvailability::class, 'id', 'id', 'availability_id', 'interviewer_id');
    }

    /**
     * Associated interviewer availability slot.
     */
    public function availability(): BelongsTo
    {
        return $this->belongsTo(InterviewerAvailability::class, 'availability_id');
    }

    /**
     * User who cancelled the booking (if cancelled).
     */
    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function canceller(): BelongsTo
    {
        return $this->cancelledBy();
    }

    protected function lookups(): array
    {
        return [
            'booking_status' => ['status_id', Status::class, 'booked', 'booking'],
        ];
    }

    public function bookingStatus(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    // interviewer / start / end are no longer copied into the booking: they come from the availability slot
    public function getInterviewerIdAttribute(): ?int
    {
        return $this->availability?->interviewer_id;
    }

    public function getStartAtAttribute()
    {
        return $this->availability?->start_at;
    }

    public function getEndAtAttribute()
    {
        return $this->availability?->end_at;
    }

    protected static function booted(): void
    {
        // active_availability_id has a UNIQUE index: one active booking per slot, cancelled slots can be re-booked
        static::saving(function (self $b) {
            $b->active_availability_id = ($b->booking_status === 'cancelled' || $b->trashed()) ? null : $b->availability_id;
        });
        static::deleting(function (self $b) {
            if (! $b->isForceDeleting()) {
                $b->newQuery()->whereKey($b->getKey())->update(['active_availability_id' => null]);
            }
        });
    }
}
