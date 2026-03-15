<?php

declare(strict_types=1);

namespace PhilipRehberger\Mask;

/**
 * Configuration for masking operations.
 */
class MaskConfig
{
    /**
     * Create a new mask configuration.
     */
    public function __construct(
        public readonly string $maskChar = '*',
        public readonly bool $preserveLength = true,
        public readonly int $visibleStart = 2,
        public readonly int $visibleEnd = 2,
    ) {}
}
