<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\HasRecordVisibility;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Interview extends Model
{
    use \App\Models\Concerns\HasLookupAttributes;

    use HasFactory, SoftDeletes, Auditable, HasRecordVisibility;

    /**
     * The table associated with the model (Recruitment Platform ERD Page 4).
     */
    protected $table = 'interviews';

    /**
     * The attributes that are mass assignable.
     * Note: application_id is the confirmed foreign key to applications.id.
     *
     * @var list<string>
     */
    protected $fillable = [
        'application_id',
        'job_interview_round_id',
        'interview_type',
        'interview_type_id',
        'status',
        'status_id',
        'meeting_link',
        'location',
        'instructions',
        'created_by',
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
        ];
    }

    /**
     * Associated interview bookings with interviewers and availability slots (ERD Page 4).
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(InterviewBooking::class, 'interview_id');
    }

    public function interviewBookings(): HasMany
    {
        return $this->bookings();
    }

    /**
     * Associated interview evaluations (ERD Page 4).
     */
    public function evaluations(): HasMany
    {
        return $this->hasMany(InterviewEvaluation::class, 'interview_id');
    }

    public function interviewEvaluations(): HasMany
    {
        return $this->evaluations();
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class, 'application_id');
    }

    /**
     * User who scheduled/created the interview.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope for candidate-specific interviews.
     * Traverses the confirmed ERD ownership chain via Query Builder subquery without requiring
     * external Candidate or Application model classes:
     * interviews.application_id -> applications.id -> applications.candidate_id -> candidate_profiles.id -> candidate_profiles.user_id
     */
    public function scopeForCandidate(Builder $query, int|string $userId): Builder
    {
        return $query->whereIn('application_id', function ($sub) use ($userId) {
            $sub->select('applications.id')
                ->from('applications')
                ->join('candidate_profiles', 'candidate_profiles.id', '=', 'applications.candidate_id')
                ->where('candidate_profiles.user_id', $userId);
        });
    }

    /**
     * Scope for interviewer-assigned interviews via interview_bookings.
     */
    public function scopeForInterviewer(Builder $query, int|string $interviewerId): Builder
    {
        return $query->whereHas('bookings.availability', function (Builder $q) use ($interviewerId) {
            $q->where('interviewer_id', $interviewerId);
        });
    }

    /**
     * Scope for upcoming interviews based on active booking start times.
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->whereNotIn('interviews.status_id', Status::idsFor(['cancelled'], 'interview'))
            ->whereHas('bookings', function (Builder $q) {
                $q->whereNotIn('interview_bookings.status_id', Status::idsFor(['cancelled'], 'booking'))
                  ->whereHas('availability', fn (Builder $a) => $a->where('start_at', '>=', Carbon::now()));
            });
    }

    /**
     * Scope for past interviews.
     */
    public function scopePast(Builder $query): Builder
    {
        return $query->whereHas('bookings.availability', function (Builder $q) {
            $q->where('start_at', '<', Carbon::now());
        });
    }

    /**
     * Scope to filter by status.
     */
    public function scopeFilterByStatus(Builder $query, ?string $status): Builder
    {
        if (!$status) {
            return $query;
        }

        return $query->whereLookup('status', $status);
    }

    /**
     * Check if the interview can be rescheduled.
     */
    public function canBeRescheduled(): bool
    {
        return strtolower((string) $this->status) !== 'completed' && strtolower((string) $this->status) !== 'cancelled';
    }

    /**
     * Check if the interview can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return strtolower((string) $this->status) !== 'completed' && strtolower((string) $this->status) !== 'cancelled';
    }

    /**
     * Check if evaluation can be submitted.
     */
    public function canBeEvaluated(): bool
    {
        return strtolower((string) $this->status) !== 'cancelled';
    }


    /**
     * interviews.round_no no longer exists: the round lives on job_interview_rounds and the
     * interview points at it through job_interview_round_id. Keep `$interview->round_no` working.
     */
    public function getRoundNoAttribute(): ?int
    {
        $round = $this->relationLoaded('jobInterviewRound') ? $this->getRelation('jobInterviewRound') : $this->jobInterviewRound;

        return $round?->round_no;
    }

    /** ORDER BY the round number of the linked job_interview_rounds row. */
    public function scopeOrderByRound(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy(
            JobInterviewRound::query()
                ->select('job_interview_rounds.round_no')
                ->whereColumn('job_interview_rounds.id', 'interviews.job_interview_round_id'),
            $direction === 'desc' ? 'desc' : 'asc'
        );
    }

    /** WHERE the linked job_interview_rounds row has this round number. */
    public function scopeWhereRoundNo(Builder $query, int $roundNo): Builder
    {
        return $query->whereHas('jobInterviewRound', fn (Builder $round) => $round->where('round_no', $roundNo));
    }

    protected function lookups(): array
    {
        return [
            'interview_type' => ['interview_type_id', InterviewType::class, 'onsite'],
            'status' => ['status_id', Status::class, 'pending', 'interview'],
        ];
    }

    public function interviewType(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(InterviewType::class, 'interview_type_id');
    }

    public function interviewStatus(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function jobInterviewRound(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(JobInterviewRound::class, 'job_interview_round_id');
    }
}
