<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApplicationAnswer extends Model
{
    protected $table = 'application_answers';
    protected $guarded = [];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class, 'application_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(JobScreeningQuestion::class, 'job_screening_question_id');
    }

    public function selectedOption(): BelongsTo
    {
        return $this->belongsTo(JobScreeningQuestionOption::class, 'selected_option_id');
    }
}
