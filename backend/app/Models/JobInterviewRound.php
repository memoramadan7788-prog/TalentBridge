<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobInterviewRound extends Model
{
    use \App\Models\Concerns\HasLookupAttributes;

    use SoftDeletes, Auditable;


    protected $table = 'job_interview_rounds';
    protected $guarded = [];

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class, 'job_id');
    }

    protected function lookups(): array
    {
        return [
            'interview_type' => ['interview_type_id', InterviewType::class, 'onsite'],
        ];
    }

    public function interviewType(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(InterviewType::class, 'interview_type_id');
    }
}
