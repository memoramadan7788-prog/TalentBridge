<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Backward-compatibility layer for string columns that became lookup foreign keys.
 *
 * Declare   protected function lookups(): array
 *           {
 *               return [
 *                   'work_mode' => ['work_mode_id', WorkMode::class, 'onsite'],
 *                   // statuses all live in the single `statuses` table: pass its type as the 4th item
 *                   'status'    => ['status_id', Status::class, 'draft', 'job'],
 *               ];
 *           }
 * Then      $job->status = 'published'   writes status_id
 *           $job->status                 reads the code back ('published')
 *           Job::whereLookup('status', ['published','closed'])   filters by FK (index friendly)
 * The JSON/array output keeps the legacy key, so API consumers do not change.
 */
trait HasLookupAttributes
{
    /**
     * Fill the lookup defaults (e.g. users.language_id = 'ar') right before the INSERT.
     *
     * This used to be a `creating` event listener, but model events are switched off by
     * WithoutModelEvents (DatabaseSeeder) and Model::withoutEvents(), so the defaults were never
     * applied and NOT NULL foreign keys failed with "doesn't have a default value".
     * performInsert() runs with or without events.
     */
    protected function performInsert(\Illuminate\Database\Eloquent\Builder $query)
    {
        foreach ($this->lookups() as $entry) {
            [$fk, $class, $default] = $entry;
            if ($default !== null && empty($this->attributes[$fk])) {
                $this->attributes[$fk] = self::resolveLookupId($class, $default, $entry[3] ?? null);
            }
        }

        return parent::performInsert($query);
    }

    private static function resolveLookupId(string $class, string $code, ?string $type): ?int
    {
        return $type !== null ? $class::idFor($code, $type) : $class::idFor($code);
    }

    public function setAttribute($key, $value)
    {
        $map = $this->lookups();
        if (isset($map[$key])) {
            $entry = $map[$key];
            [$fk, $class] = $entry;
            $this->attributes[$fk] = ($value === null || $value === '')
                ? null
                : (is_numeric($value) ? (int) $value : self::resolveLookupId($class, (string) $value, $entry[3] ?? null));
            return $this;
        }

        return parent::setAttribute($key, $value);
    }

    public function getAttribute($key)
    {
        $map = $this->lookups();
        if ($key !== null && isset($map[$key])) {
            [$fk, $class] = $map[$key];
            return $class::keyFor(isset($this->attributes[$fk]) ? (int) $this->attributes[$fk] : null);
        }

        return parent::getAttribute($key);
    }

    public function attributesToArray()
    {
        $array = parent::attributesToArray();
        foreach ($this->lookups() as $legacy => $entry) {
            [$fk, $class] = $entry;
            $array[$legacy] = $class::keyFor(isset($this->attributes[$fk]) ? (int) $this->attributes[$fk] : null);
        }

        return $array;
    }

    public function scopeWhereLookup(Builder $query, string $legacy, string|array $codes): Builder
    {
        $entry = $this->lookups()[$legacy];
        [$fk, $class] = $entry;
        $type = $entry[3] ?? null;
        $ids = $type !== null ? $class::idsFor((array) $codes, $type) : $class::idsFor((array) $codes);

        return $query->whereIn($this->qualifyColumn($fk), $ids);
    }

    abstract protected function lookups(): array;
}