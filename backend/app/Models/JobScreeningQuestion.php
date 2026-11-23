<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class JobScreeningQuestion extends Model
{
    use \App\Models\Concerns\HasLookupAttributes;

    use SoftDeletes, Auditable;

    protected $table = 'job_screening_questions';
    protected $guarded = [];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class, 'job_id');
    }

    protected function lookups(): array
    {
        return [
            'type' => ['screening_question_type_id', ScreeningQuestionType::class, 'text'],
        ];
    }

    public function screeningQuestionType(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ScreeningQuestionType::class, 'screening_question_type_id');
    }

    public function options(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(JobScreeningQuestionOption::class, 'job_screening_question_id')->orderBy('sort_order');
    }

    /**
     * `options` is no longer a JSON column: each option is a row in job_screening_question_options.
     * Accepts ['Yes', 'No'] or [['label' => 'Yes', 'value' => 'y'], ...].
     */
    public static function createWithOptions(array $attributes, ?array $options = null): self
    {
        unset($attributes['options']);
        $question = static::create($attributes);

        foreach (array_values($options ?? []) as $i => $option) {
            $label = is_array($option) ? ($option['label'] ?? $option['value'] ?? null) : $option;
            if ($label === null || $label === '') {
                continue;
            }
            $question->options()->create([
                'label' => (string) $label,
                'value' => is_array($option) ? ($option['value'] ?? null) : null,
                'sort_order' => is_array($option) ? ($option['sort_order'] ?? $i) : $i,
            ]);
        }

        return $question;
    }
}
