<?php

declare(strict_types=1);

namespace PhilipRehberger\Mask\Concerns;

/**
 * Core string masking algorithm.
 */
trait MasksStrings
{
    /**
     * Mask a string, preserving visible characters at the start and end.
     */
    protected static function maskString(
        string $value,
        int $visibleStart,
        int $visibleEnd,
        string $maskChar,
        bool $preserveLength,
    ): string {
        $length = mb_strlen($value);

        if ($length === 0) {
            return '';
        }

        if ($length <= $visibleStart + $visibleEnd) {
            return str_repeat($maskChar, $preserveLength ? $length : 3);
        }

        $start = mb_substr($value, 0, $visibleStart);
        $end = $visibleEnd > 0 ? mb_substr($value, -$visibleEnd) : '';
        $middleLength = $length - $visibleStart - $visibleEnd;

        $masked = $preserveLength
            ? str_repeat($maskChar, $middleLength)
            : str_repeat($maskChar, 3);

        return $start.$masked.$end;
    }
}
