<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\HasRecordVisibility;
use App\Traits\HasPublishStatus;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    use \App\Models\Concerns\HasLookupAttributes;

    use SoftDeletes, Auditable, HasRecordVisibility;


    protected $table = 'exams';
    protected $guarded = [];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function jobExams(): HasMany
    {
        return $this->hasMany(JobExam::class, 'exam_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'exam_id');
    }

    public function examAttempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class, 'exam_id');
    }

    protected function lookups(): array
    {
        return [
            'status' => ['status_id', Status::class, 'draft', 'exam'],
        ];
    }

    public function examStatus(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }
}
