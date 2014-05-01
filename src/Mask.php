<?php

declare(strict_types=1);

namespace PhilipRehberger\Mask;

use PhilipRehberger\Mask\Concerns\MasksArrays;
use PhilipRehberger\Mask\Concerns\MasksStrings;

/**
 * Static API for masking sensitive data in strings, arrays, and JSON.
 */
class Mask
{
    use MasksArrays;
    use MasksStrings;

    private static ?MaskConfig $config = null;

    /**
     * Set global masking configuration.
     */
    public static function configure(MaskConfig $config): void
    {
        self::$config = $config;
    }

    /**
     * Reset configuration to defaults.
     */
    public static function resetConfig(): void
    {
        self::$config = null;
    }

    /**
     * Get the current configuration or default.
     */
    private static function config(): MaskConfig
    {
        return self::$config ?? new MaskConfig;
    }

    /**
     * Mask an email address.
     *
     * Example: "john@example.com" becomes "j***@e*****.com"
     */
    public static function email(string $email): string
    {
        if ($email === '') {
            return '';
        }

        $parts = explode('@', $email);

        if (count($parts) !== 2) {
            return self::string($email);
        }

        [$local, $domain] = $parts;

        $config = self::config();

        $maskedLocal = self::maskEmailPart($local, $config->maskChar);

        $dotPos = strrpos($domain, '.');
        if ($dotPos === false) {
            $maskedDomain = self::maskEmailPart($domain, $config->maskChar);

            return $maskedLocal.'@'.$maskedDomain;
        }

        $domainName = substr($domain, 0, $dotPos);
        $tld = substr($domain, $dotPos);

        $maskedDomain = self::maskEmailPart($domainName, $config->maskChar);

        return $maskedLocal.'@'.$maskedDomain.$tld;
    }

    /**
     * Mask a local or domain part of an email, showing only the first character.
     */
    private static function maskEmailPart(string $part, string $maskChar): string
    {
        $length = mb_strlen($part);

        if ($length <= 1) {
            return $part;
        }

        return mb_substr($part, 0, 1).str_repeat($maskChar, $length - 1);
    }

    /**
     * Mask a phone number, preserving country code and last 4 digits.
     *
     * Example: "+1-555-123-4567" becomes "+1-555-***-4567"
     */
    public static function phone(string $phone): string
    {
        if ($phone === '') {
            return '';
        }

        $config = self::config();
        $digits = preg_replace('/\D/', '', $phone);

        if ($digits === null || strlen($digits) < 4) {
            return str_repeat($config->maskChar, mb_strlen($phone));
        }

        $last4 = substr($digits, -4);
        $result = $phone;

        $pos = mb_strlen($phone);
        $digitCount = 0;

        for ($i = mb_strlen($phone) - 1; $i >= 0; $i--) {
            $char = mb_substr($phone, $i, 1);
            if (ctype_digit($char)) {
                $digitCount++;
                if ($digitCount > 4 && $digitCount <= strlen($digits) - 3) {
                    $result = mb_substr($result, 0, $i).$config->maskChar.mb_substr($result, $i + 1);
                }
            }
        }

        return $result;
    }

    /**
     * Mask a credit card number, showing first 4 and last 4 digits.
     *
     * Example: "4111 1234 5678 1111" becomes "4111 **** **** 1111"
     */
    public static function creditCard(string $number): string
    {
        if ($number === '') {
            return '';
        }

        $config = self::config();
        $digits = preg_replace('/\D/', '', $number);

        if ($digits === null || strlen($digits) < 8) {
            return str_repeat($config->maskChar, mb_strlen($number));
        }

        $result = $number;
        $digitIndex = 0;
        $totalDigits = strlen($digits);

        for ($i = 0; $i < mb_strlen($number); $i++) {
            $char = mb_substr($number, $i, 1);
            if (ctype_digit($char)) {
                if ($digitIndex >= 4 && $digitIndex < $totalDigits - 4) {
                    $result = mb_substr($result, 0, $i).$config->maskChar.mb_substr($result, $i + 1);
                }
                $digitIndex++;
            }
        }

        return $result;
    }

    /**
     * Mask an IP address, showing first two octets for IPv4.
     *
     * Example: "192.168.1.100" becomes "192.168.*.*"
     */
    public static function ip(string $ip): string
    {
        if ($ip === '') {
            return '';
        }

        $config = self::config();

        if (str_contains($ip, ':')) {
            $parts = explode(':', $ip);
            $visible = min(2, count($parts));

            $masked = array_merge(
                array_slice($parts, 0, $visible),
                array_fill(0, count($parts) - $visible, $config->maskChar),
            );

            return implode(':', $masked);
        }

        $parts = explode('.', $ip);

        if (count($parts) !== 4) {
            return self::string($ip);
        }

        return $parts[0].'.'.$parts[1].'.'.$config->maskChar.'.'.$config->maskChar;
    }

    /**
     * Mask a generic string with configurable visible start and end characters.
     *
     * Example: "SensitiveData" with defaults becomes "Se*********ta"
     */
    public static function string(string $value, int $visibleStart = 2, int $visibleEnd = 2): string
    {
        $config = self::config();

        return self::maskString(
            $value,
            $visibleStart,
            $visibleEnd,
            $config->maskChar,
            $config->preserveLength,
        );
    }

    /**
     * Deep-mask specified keys in an array.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $keys
     * @return array<string, mixed>
     */
    public static function array(array $data, array $keys): array
    {
        $config = self::config();

        return self::maskArrayRecursive(
            $data,
            $keys,
            $config->maskChar,
            $config->preserveLength,
            $config->visibleStart,
            $config->visibleEnd,
        );
    }

    /**
     * Parse JSON, mask specified keys, and re-encode.
     *
     * @param  array<int, string>  $keys
     */
    public static function json(string $json, array $keys): string
    {
        if ($json === '') {
            return '';
        }

        $data = json_decode($json, true);

        if (! is_array($data)) {
            return $json;
        }

        /** @var array<string, mixed> $data */
        $masked = self::array($data, $keys);

        $result = json_encode($masked, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return $result !== false ? $result : $json;
    }
}
