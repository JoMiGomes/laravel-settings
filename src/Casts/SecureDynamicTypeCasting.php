<?php

namespace YellowParadox\LaravelSettings\Casts;

use YellowParadox\LaravelSettings\Models\Setting;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use JsonException;

class SecureDynamicTypeCasting implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     *
     * @throws JsonException
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): array|bool|float|int|string|null|object
    {
        if ($value === null) {
            return null;
        }

        return match ($attributes['type'] ?? null) {
            Setting::TYPE_ARRAY => Arr::wrap($this->decodeJson($value, true)),
            Setting::TYPE_BOOLEAN => (bool) $this->decodeJson($value),
            Setting::TYPE_DATETIME => Carbon::parse($this->decodeJson($value)),
            Setting::TYPE_COLLECTION => new Collection(Arr::wrap($this->decodeJson($value, true))),
            Setting::TYPE_DOUBLE => (float) $this->decodeJson($value),
            Setting::TYPE_INTEGER, Setting::TYPE_STRING => $this->decodeJson($value),
            Setting::TYPE_OBJECT => $this->decodeObject($value),

            default => $value,
        };
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     *
     * @throws JsonException
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): array|bool|float|int|string|null
    {
        if ($value === null) {
            return null;
        }

        return match ($attributes['type'] ?? null) {
            Setting::TYPE_COLLECTION => $value instanceof Collection
                ? $value->toJson()
                : $this->encodeJson($value),
            Setting::TYPE_ARRAY, Setting::TYPE_STRING, Setting::TYPE_INTEGER => $this->encodeJson($value),
            Setting::TYPE_BOOLEAN => $this->encodeJson((bool) $value),
            Setting::TYPE_DATETIME => $value instanceof Carbon ? $this->encodeJson($value->toDateTimeString()) :
                (string) $value,
            Setting::TYPE_DOUBLE => (float) $value,
            Setting::TYPE_OBJECT => $this->encodeObject($value),

            default => $value,
        };
    }

    /**
     * Decode JSON.
     *
     * @throws JsonException
     */
    private function decodeJson(string $value, bool $associative = false): mixed
    {
        return json_decode($value, $associative, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Encode JSON.
     *
     * @throws JsonException
     */
    private function encodeJson(mixed $value): bool|string
    {
        return json_encode($value, JSON_THROW_ON_ERROR);
    }

    /**
     * Decode object from JSON (secure alternative to unserialize).
     *
     * @throws JsonException
     */
    private function decodeObject(string $value): object
    {
        $decoded = $this->decodeJson($value);
        
        return is_object($decoded) ? $decoded : (object) $decoded;
    }

    /**
     * Encode object to JSON (secure alternative to serialize).
     *
     * @throws JsonException
     */
    private function encodeObject(object $value): string
    {
        return $this->encodeJson($value);
    }
}
