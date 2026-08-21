<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Website-level key/value settings (platform-wide, not per-store).
 *
 * Values are JSON-encoded on write and decoded on read, so a setting can hold
 * a plain string (e.g. footer_about) or a structured array (e.g. header_menu,
 * announcement_items). The full map is cached forever and flushed on any write.
 */
class SiteSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public const CACHE_KEY = 'site_settings.map';

    protected static function booted(): void
    {
        // Any write path (including Filament) invalidates the cached map.
        static::saved(fn () => static::flush());
        static::deleted(fn () => static::flush());
    }

    /**
     * [key => decoded value] for every setting, cached until a write flushes it.
     */
    public static function map(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return static::query()
                ->pluck('value', 'key')
                ->map(fn ($raw) => static::decode($raw))
                ->all();
        });
    }

    /**
     * Read a single setting, falling back to $default when missing.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $map = static::map();

        return array_key_exists($key, $map) ? $map[$key] : $default;
    }

    /**
     * Create/update a single setting and flush the cache.
     */
    public static function put(string $key, mixed $value): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => static::encode($value)],
        );

        static::flush();
    }

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    protected static function decode(?string $raw): mixed
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        $decoded = json_decode($raw, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $raw;
    }

    protected static function encode(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
