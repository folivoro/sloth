<?php

declare(strict_types=1);
namespace Sloth\Model\Meta;

/**
 * Guards the meta `value` accessor against unserialize warnings.
 *
 * Corcel's `Meta::getValueAttribute()` blindly calls `unserialize()` on every
 * stored `meta_value`. In PHP 8 a plain-text value (e.g. WordPress menu item
 * meta keys like `_menu_item_object_id` storing a 9-byte string) raises a
 * `Warning` that is not caught by `try/catch`. This trait re-implements the
 * accessor with an `is_serialized()` guard so un-serialized strings are
 * returned untouched, mirroring WordPress' own `maybe_unserialize()`.
 *
 * @see \Corcel\Model\Meta\Meta Original accessor
 * @see https://developer.wordpress.org/reference/functions/maybe_unserialize/
 */
trait HasSafeMetaValue
{
    /**
     * Get the unserialized meta value when the stored value is serialized.
     *
     * @return mixed The unserialized value, or the raw string when the value
     *               is not (validly) serialized
     */
    public function getValueAttribute(): mixed
    {
        $value = $this->getAttribute('meta_value');

        if (!is_string($value) || !self::isSerializedString($value)) {
            return $value;
        }

        $unserialized = @unserialize($value);

        return $unserialized === false ? $value : $unserialized;
    }

    /**
     * Determine whether a string looks like a PHP-serialized value.
     *
     * Ports WordPress' `is_serialized()` with strict validation so it works
     * outside a WP runtime and avoids `unserialize()` warnings for
     * plain-text meta values.
     *
     * @param  string $data The raw meta value
     * @return bool   True when the value is likely serialized
     */
    private static function isSerializedString(string $data): bool
    {
        $data = trim($data);

        if ($data === 'N;') {
            return true;
        }

        $length = strlen($data);

        if ($length < 4 || $data[1] !== ':') {
            return false;
        }

        $lastc = $data[$length - 1];

        if ($lastc !== ';' && $lastc !== '}') {
            return false;
        }

        return match ($data[0]) {
            's'           => $data[$length - 2] === '"',
            'a', 'O'      => (bool) preg_match('/^' . $data[0] . ':[0-9]+:/s', $data),
            'b', 'i', 'd' => true,
            default       => false,
        };
    }
}
