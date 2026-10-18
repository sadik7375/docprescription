<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
        'description',
    ];

    /**
     * Retrieve a setting value by key with caching.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("system_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            if (!$setting) {
                return $default;
            }

            return static::castValue($setting->value, $setting->type);
        });
    }

    /**
     * Store or update a setting value.
     */
    public static function set(string $key, mixed $value, string $group = 'general', string $type = 'string', ?string $description = null): self
    {
        $serializedValue = is_array($value) || is_object($value) ? json_encode($value) : (string)$value;

        $setting = static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $serializedValue,
                'group' => $group,
                'type' => $type,
                'description' => $description,
            ]
        );

        Cache::forget("system_setting_{$key}");
        Cache::forget("system_settings_group_{$group}");

        return $setting;
    }

    /**
     * Retrieve all settings in a group as key => value array.
     */
    public static function getGroup(string $group): array
    {
        return Cache::remember("system_settings_group_{$group}", 3600, function () use ($group) {
            $settings = static::where('group', $group)->get();
            $result = [];
            foreach ($settings as $setting) {
                $result[$setting->key] = static::castValue($setting->value, $setting->type);
            }
            return $result;
        });
    }

    /**
     * Cast raw string value to its specified type.
     */
    private static function castValue(?string $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'boolean', 'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer', 'int' => (int)$value,
            'float' => (float)$value,
            'json', 'array' => json_decode($value, true),
            default => $value,
        };
    }
}
