<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A job title sits under a SUBCATEGORY (Software > Backend > "Node.js Backend Developer"). */
class JobTitle extends LookupModel
{
    protected $table = 'job_titles';
    protected static string $keyColumn = 'name';
    protected static bool $autoCreate = true;

    protected static function booted(): void
    {
        parent::booted();

        static::saving(function (JobTitle $title) {
            if ($title->job_category_id && JobCategory::query()->whereKey($title->job_category_id)->whereNull('parent_id')->exists()) {
                throw new \InvalidArgumentException('A job title must belong to a subcategory, not to a top-level category.');
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(JobCategory::class, 'job_category_id');
    }

    public function matchedJobs(): HasMany
    {
        return $this->hasMany(MatchedJob::class, 'job_title_id');
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class, 'job_title_id');
    }
}
