<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradingResult extends Model
{
    use \App\Models\Concerns\HasLookupAttributes;

    use SoftDeletes, Auditable;


    protected $table = 'grading_results';
    protected $guarded = [];

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(ExamAttempt::class, 'attempt_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    protected function lookups(): array
    {
        return [
            'grading_method' => ['grading_method_id', GradingMethod::class, null],
            'status' => ['status_id', Status::class, 'pending', 'grading'],
        ];
    }

    public function gradingMethod(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(GradingMethod::class, 'grading_method_id');
    }

    public function gradingStatus(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }
}
