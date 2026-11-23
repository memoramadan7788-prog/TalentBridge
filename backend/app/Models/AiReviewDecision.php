<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiReviewDecision extends Model
{
    use \App\Models\Concerns\HasLookupAttributes;

    use SoftDeletes, Auditable;


    protected $table = 'ai_review_decisions';
    protected $guarded = [];

    protected $casts = [
        'decided_at' => 'datetime',
    ];

    public function matchResult(): BelongsTo
    {
        return $this->belongsTo(CvMatchResult::class, 'match_result_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    protected function lookups(): array
    {
        return [
            'decision' => ['review_decision_id', ReviewDecision::class, 'accept'],
        ];
    }

    public function reviewDecision(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ReviewDecision::class, 'review_decision_id');
    }
}
