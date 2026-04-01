<?php

declare(strict_types=1);

namespace PhilipRehberger\Mask;

/**
 * Declarative redaction policy for defining reusable masking rules.
 *
 * Supports field paths (dot notation and wildcards) and regex-based pattern matching
 * against field names, with configurable masking methods.
 */
class RedactionPolicy
{
    /** @var array<int, array{path: string, method: string}> */
    private array $rules = [];

    /** @var array<int, array{regex: string, method: string}> */
    private array $patterns = [];

    /**
     * Create a new redaction policy instance.
     */
    public static function create(): self
    {
        return new self;
    }

    /**
     * Register a masking rule for a specific field path.
     *
     * Supports dot notation (e.g., "user.email") and wildcards (e.g., "*.secret").
     *
     * @param  string  $path  The field path to match
     * @param  string  $method  The masking method: 'email', 'phone', 'card', 'ip', 'iban', 'full', or 'partial'
     */
    public function maskField(string $path, string $method): self
    {
        $this->rules[] = ['path' => $path, 'method' => $method];

        return $this;
    }

    /**
     * Register a pattern-based rule using a regex matched against field names.
     *
     * @param  string  $regex  Regular expression to match against field names
     * @param  string  $method  The masking method: 'email', 'phone', 'card', 'ip', 'iban', 'full', or 'partial'
     */
    public function maskPattern(string $regex, string $method): self
    {
        $this->patterns[] = ['regex' => $regex, 'method' => $method];

        return $this;
    }

    /**
     * Apply all registered rules to an array, returning a masked copy.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function apply(array $data): array
    {
        foreach ($this->rules as $rule) {
            $data = $this->applyFieldRule($data, $rule['path'], $rule['method']);
        }

        if ($this->patterns !== []) {
            $data = $this->applyPatterns($data);
        }

        return $data;
    }

    /**
     * Merge another policy into this one, combining all rules.
     */
    public function merge(self $other): self
    {
        $this->rules = array_merge($this->rules, $other->rules);
        $this->patterns = array_merge($this->patterns, $other->patterns);

        return $this;
    }

    /**
     * Apply a single field rule to the data array.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function applyFieldRule(array $data, string $path, string $method): array
    {
        $segments = explode('.', $path);

        return $this->applyAtPath($data, $segments, $method);
    }

    /**
     * Recursively traverse the array and apply masking at the target path.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $segments
     * @return array<string, mixed>
     */
    private function applyAtPath(array $data, array $segments, string $method): array
    {
        if ($segments === []) {
            return $data;
        }

        $current = array_shift($segments);

        if ($current === '*') {
            foreach ($data as $key => $value) {
                if ($segments === []) {
                    if (is_string($value)) {
                        $data[$key] = $this->maskValue($value, $method);
                    }
                } elseif (is_array($value)) {
                    $data[$key] = $this->applyAtPath($value, $segments, $method);
                }
            }

            return $data;
        }

        if (! array_key_exists($current, $data)) {
            return $data;
        }

        if ($segments === []) {
            if (is_string($data[$current])) {
                $data[$current] = $this->maskValue($data[$current], $method);
            }

            return $data;
        }

        if (is_array($data[$current])) {
            $data[$current] = $this->applyAtPath($data[$current], $segments, $method);
        }

        return $data;
    }

    /**
     * Apply pattern-based rules recursively to all fields in the data.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function applyPatterns(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->applyPatterns($value);

                continue;
            }

            if (! is_string($value)) {
                continue;
            }

            foreach ($this->patterns as $pattern) {
                if (preg_match($pattern['regex'], (string) $key) === 1) {
                    $data[$key] = $this->maskValue($value, $pattern['method']);

                    break;
                }
            }
        }

        return $data;
    }

    /**
     * Mask a value using the specified method.
     */
    private function maskValue(string $value, string $method): string
    {
        return match ($method) {
            'email' => Mask::email($value),
            'phone' => Mask::phone($value),
            'card' => Mask::creditCard($value),
            'ip' => Mask::ip($value),
            'iban' => Mask::iban($value),
            'full' => str_repeat('*', mb_strlen($value)),
            default => Mask::string($value),
        };
    }
}
