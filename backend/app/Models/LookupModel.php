<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Base class for every reference table (statuses, types, currencies, ...).
 * Keeps a per-request id<->key cache so mapping "published" <-> 3 never costs a query twice.
 */
abstract class LookupModel extends Model
{
    protected $guarded = [];

    /** column that identifies a row: 'code' for fixed vocabularies, 'name' for open-ended ones */
    protected static string $keyColumn = 'code';

    /** open-ended lookups create the row when an unknown value is written */
    protected static bool $autoCreate = false;

    /** @var array<class-string, array<string,int>> */
    private static array $cache = [];

    protected static function booted(): void
    {
        $class = static::class;
        // unset (not []): an empty array counts as "already loaded", so the map would never be rebuilt
        $flush = function () use ($class) {
            unset(self::$cache[$class]);
        };
        static::saved($flush);
        static::deleted($flush);
    }

    private static function map(): array
    {
        return self::$cache[static::class] ??= static::query()->pluck('id', static::$keyColumn)->all();
    }

    public static function idFor(?string $key): ?int
    {
        if ($key === null || $key === '') {
            return null;
        }
        $id = static::map()[$key] ?? null;
        if ($id === null) {
            // utf8mb4_unicode_ci treats "Mid-Level X" and "mid-level x" as the same row, a PHP array key does not.
            // Check the DB before creating, otherwise the unique index throws a duplicate-entry error.
            $id = static::query()->where(static::$keyColumn, $key)->value('id');
            if ($id !== null) {
                static::map();
                self::$cache[static::class][$key] = (int) $id;
            }
        }
        if ($id === null && static::$autoCreate) {
            $row = [static::$keyColumn => $key];
            if (static::$keyColumn === 'code') {
                $row['name'] = Str::headline($key);
            }
            $id = static::query()->create($row)->id;
            static::map();
            self::$cache[static::class][$key] = (int) $id;
        }
        if ($id === null) {
            throw new \InvalidArgumentException(sprintf('Unknown %s value "%s".', class_basename(static::class), $key));
        }
        return (int) $id;
    }

    /** @param  array<int,string>  $keys */
    public static function idsFor(array $keys): array
    {
        $map = static::map();
        return array_values(array_filter(array_map(fn ($k) => $map[$k] ?? null, $keys)));
    }

    public static function keyFor(?int $id): ?string
    {
        if ($id === null) {
            return null;
        }
        $key = array_search($id, static::map(), true);
        return $key === false ? null : (string) $key;
    }
}