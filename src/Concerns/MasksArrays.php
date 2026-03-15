<?php

declare(strict_types=1);

namespace PhilipRehberger\Mask\Concerns;

/**
 * Recursive array traversal for masking.
 */
trait MasksArrays
{
    /**
     * Recursively mask specified keys in an array.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $keys
     * @return array<string, mixed>
     */
    protected static function maskArrayRecursive(
        array $data,
        array $keys,
        string $maskChar,
        bool $preserveLength,
        int $visibleStart,
        int $visibleEnd,
    ): array {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = static::maskArrayRecursive(
                    $value,
                    $keys,
                    $maskChar,
                    $preserveLength,
                    $visibleStart,
                    $visibleEnd,
                );
            } elseif (in_array($key, $keys, true) && is_string($value)) {
                $data[$key] = static::maskString(
                    $value,
                    $visibleStart,
                    $visibleEnd,
                    $maskChar,
                    $preserveLength,
                );
            }
        }

        return $data;
    }
}
