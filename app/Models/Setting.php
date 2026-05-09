<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        if ($setting === null) {
            return $default;
        }

        $decoded = json_decode($setting->value, true);

        return $decoded !== null ? $decoded : $setting->value;
    }

    public static function set(string $key, mixed $value): void
    {
        $encoded = is_string($value) ? $value : json_encode($value);

        static::updateOrCreate(
            ['key' => $key],
            ['value' => $encoded],
        );
    }
}
