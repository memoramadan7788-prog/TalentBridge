<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobCategory extends Model
{
    use HasFactory, SoftDeletes, Auditable;


    protected $table = 'job_categories';
    protected $guarded = [];

    protected static function booted(): void
    {
        // two levels only: category -> subcategory (job titles hang under the subcategory)
        static::saving(function (JobCategory $category) {
            if ($category->parent_id === null) {
                return;
            }
            if (static::query()->whereKey($category->parent_id)->whereNotNull('parent_id')->exists()) {
                throw new \InvalidArgumentException('A subcategory cannot have its own subcategories.');
            }
            if ($category->exists && static::query()->where('parent_id', $category->getKey())->exists()) {
                throw new \InvalidArgumentException('A category that has subcategories cannot become a subcategory.');
            }
        });
    }

    public function scopeCategories($query)
    {
        return $query->whereNull($this->qualifyColumn('parent_id'));
    }

    public function scopeSubcategories($query)
    {
        return $query->whereNotNull($this->qualifyColumn('parent_id'));
    }

    public function subcategories(): HasMany
    {
        return $this->children();
    }

    public function jobTitles(): HasMany
    {
        return $this->hasMany(JobTitle::class, 'job_category_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(JobCategory::class, 'parent_id');
    }

    public function jobs(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(Job::class, JobTitle::class, 'job_category_id', 'job_title_id');
    }

    public function salaryRecords(): HasMany
    {
        return $this->hasMany(SalaryRecord::class, 'category_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(JobCategory::class, 'parent_id');
    }
}
