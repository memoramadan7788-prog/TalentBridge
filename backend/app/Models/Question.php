<?php

namespace App\Models;

use App\Models\Concerns\HasLookupAttributes;
use App\Traits\Auditable;
use App\Traits\HasPublishStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasLookupAttributes;
    use SoftDeletes, Auditable, HasPublishStatus;

    protected $table = 'questions';

    protected $guarded = [];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function questionOptions(): HasMany
    {
        return $this->hasMany(QuestionOption::class, 'question_id');
    }

    public function options(): HasMany
    {
        return $this->questionOptions();
    }

    public function questionTestCases(): HasMany
    {
        return $this->hasMany(QuestionTestCase::class, 'question_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class, 'question_id');
    }

    protected function lookups(): array
    {
        return [
            'question_type' => ['question_type_id', QuestionType::class, 'mcq'],
            'source' => ['question_source_id', QuestionSource::class, 'manual'],
            'status' => ['status_id', Status::class, 'draft', 'question'],
        ];
    }

    public function questionType(): BelongsTo
    {
        return $this->belongsTo(QuestionType::class, 'question_type_id');
    }

    public function questionSource(): BelongsTo
    {
        return $this->belongsTo(QuestionSource::class, 'question_source_id');
    }

    public function questionStatus(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }
}
