<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $fillable = ['group', 'key', 'value', 'type', 'description'];

    protected static string $cacheKey = 'system_settings';

    protected static int $cacheTtl = 3600;

    public static function get(string $key, mixed $default = null): mixed
    {
        $settings = static::allCached();
        $setting = $settings->firstWhere('key', $key);

        if (! $setting) {
            return $default;
        }

        return static::castValue($setting->value, $setting->type);
    }

    public static function set(string $key, mixed $value): void
    {
        $setting = static::where('key', $key)->first();

        if (! $setting) {
            return;
        }

        $setting->update(['value' => static::serializeValue($value, $setting->type)]);
        static::flushCache();
    }

    public static function getGroup(string $group): Collection
    {
        return static::allCached()
            ->where('group', $group)
            ->mapWithKeys(fn (self $s) => [$s->key => static::castValue($s->value, $s->type)]);
    }

    public static function flushCache(): void
    {
        Cache::forget(static::$cacheKey);
    }

    protected static function allCached(): Collection
    {
        return Cache::remember(static::$cacheKey, static::$cacheTtl, function () {
            return static::all();
        });
    }

    protected static function castValue(?string $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'integer' => (int) $value,
            'float' => (float) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    protected static function serializeValue(mixed $value, string $type): ?string
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'boolean' => $value ? 'true' : 'false',
            'json' => is_string($value) ? $value : json_encode($value),
            default => (string) $value,
        };
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::flushCache());
        static::deleted(fn () => static::flushCache());
    }
}
