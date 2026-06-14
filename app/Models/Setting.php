<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Simple key/value application settings, cached as a single map. Each known key
 * has a typed default in defaults(); get() casts the stored string back to the
 * default's type so callers get a real int/bool.
 */
class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    private const CACHE_KEY = 'settings.all';

    /**
     * Known settings and their fallback defaults (and, implicitly, their types).
     *
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            'app_display_name' => config('app.name'),
            'items_per_page' => 25,
            'trash_retention_days' => 90,
            'enforce_2fa_for_admins' => (bool) config('auth.two_factor.enforce_for_admins'),
        ];
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $fallback = $default ?? (static::defaults()[$key] ?? null);
        $raw = static::allCached()[$key] ?? null;

        if ($raw === null) {
            return $fallback;
        }

        return match (true) {
            is_bool($fallback) => $raw === '1',
            is_int($fallback) => (int) $raw,
            default => $raw,
        };
    }

    public static function set(string $key, mixed $value): void
    {
        $stored = is_bool($value) ? ($value ? '1' : '0') : (string) $value;
        static::updateOrCreate(['key' => $key], ['value' => $stored]);
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return array<string, string>
     */
    private static function allCached(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            // Tolerate the table not existing yet (e.g. during migrate, when this
            // is reached via the app_display_name override in AppServiceProvider).
            try {
                return static::pluck('value', 'key')->all();
            } catch (\Throwable) {
                return [];
            }
        });
    }
}
