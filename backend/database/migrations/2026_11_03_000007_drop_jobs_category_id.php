<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * jobs.category_id duplicated what job_title_id -> job_titles.job_category_id -> job_categories already says.
 * The category of a job now comes ONLY from its job title.
 *
 * Existing jobs are mapped so no category is lost:
 *   - the job's `title` is matched (case-insensitive) with job_titles.name, otherwise a job title with that name is created
 *   - the title is put under the job's old category; if that was a top-level category, under its "General" subcategory
 *     (created if missing), because titles can only live under a subcategory
 *   - an existing title that has no subcategory yet takes the job's one; a title already placed elsewhere is reused as is
 *
 * Not reversible: take a DB backup first.
 */
return new class extends Migration
{
    /** @var array<int,int> category id => subcategory id */
    private array $subCache = [];

    public function up(): void
    {
        if (Schema::hasColumn('jobs', 'category_id')) {
            $pairs = DB::table('jobs')->whereNull('job_title_id')->whereNotNull('title')
                ->select('title', 'category_id')->distinct()->get();

            foreach ($pairs as $row) {
                $name = trim((string) $row->title);
                if ($name === '') {
                    continue;
                }
                $subId = $row->category_id ? $this->subcategoryFor((int) $row->category_id) : null;

                $titleId = DB::table('job_titles')->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->value('id');
                if (! $titleId) {
                    $titleId = DB::table('job_titles')->insertGetId([
                        'name' => $name, 'job_category_id' => $subId,
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                } elseif ($subId) {
                    DB::table('job_titles')->where('id', $titleId)->whereNull('job_category_id')->update(['job_category_id' => $subId]);
                }

                DB::table('jobs')->whereNull('job_title_id')->where('title', $row->title)
                    ->when($row->category_id === null,
                        fn ($q) => $q->whereNull('category_id'),
                        fn ($q) => $q->where('category_id', $row->category_id))
                    ->update(['job_title_id' => $titleId]);
            }
        }

        $this->attempt(fn () => Schema::table('jobs', fn (Blueprint $t) => $t->dropForeign(['category_id'])));
        $this->attempt(fn () => Schema::table('jobs', fn (Blueprint $t) => $t->dropIndex(['category_id'])));
        if (Schema::hasColumn('jobs', 'category_id')) {
            Schema::table('jobs', fn (Blueprint $t) => $t->dropColumn('category_id'));
        }
    }

    public function down(): void
    {
        throw new \RuntimeException('This migration is not reversible. Restore the DB backup instead.');
    }

    private function attempt(\Closure $fn): void
    {
        try {
            $fn();
        } catch (\Throwable) {
        }
    }

    /** the subcategory a job title can live under for this (old) category */
    private function subcategoryFor(int $categoryId): ?int
    {
        if (isset($this->subCache[$categoryId])) {
            return $this->subCache[$categoryId];
        }
        $cat = DB::table('job_categories')->where('id', $categoryId)->first();
        if (! $cat) {
            return null;
        }
        if ($cat->parent_id !== null) {
            return $this->subCache[$categoryId] = (int) $cat->id;
        }
        $id = DB::table('job_categories')->where('parent_id', $cat->id)->where('name', 'General')->value('id')
            ?? DB::table('job_categories')->insertGetId([
                'name' => 'General', 'parent_id' => $cat->id,
                'created_at' => now(), 'updated_at' => now(),
            ]);

        return $this->subCache[$categoryId] = (int) $id;
    }
};
