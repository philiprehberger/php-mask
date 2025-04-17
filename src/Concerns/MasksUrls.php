<?php

declare(strict_types=1);

namespace PhilipRehberger\Mask\Concerns;

/**
 * URL and JWT masking algorithms.
 */
trait MasksUrls
{
    /**
     * Mask a URL by redacting query parameter values and embedded credentials.
     *
     * Preserves domain, path, and query parameter names. Masks username/password
     * in URLs like "user:pass@host" and each query parameter value (keeping the
     * first character visible).
     *
     * Example: "https://user:pass@example.com/path?token=abc123" becomes
     *
     *          "https://u***:p***@example.com/path?token=a*****"
     */
    public static function url(string $url): string
    {
        if ($url === '') {
            return '';
        }

        $parsed = parse_url($url);

        if ($parsed === false || ! isset($parsed['host'])) {
            return self::string($url);
        }

        $result = '';

        if (isset($parsed['scheme'])) {
            $result .= $parsed['scheme'].'://';
        }

        if (isset($parsed['user'])) {
            $result .= self::maskUrlPart($parsed['user']);

            if (isset($parsed['pass'])) {
                $result .= ':'.self::maskUrlPart($parsed['pass']);
            }

            $result .= '@';
        }

        $result .= $parsed['host'];

        if (isset($parsed['port'])) {
            $result .= ':'.$parsed['port'];
        }

        if (isset($parsed['path'])) {
            $result .= $parsed['path'];
        }

        if (isset($parsed['query'])) {
            $params = [];
            parse_str($parsed['query'], $params);

            $maskedParams = [];

            foreach ($params as $key => $value) {
                if (is_string($value)) {
                    $maskedParams[] = $key.'='.self::maskUrlPart($value);
                } else {
                    $maskedParams[] = $key.'=';
                }
            }

            $result .= '?'.implode('&', $maskedParams);
        }

        if (isset($parsed['fragment'])) {
            $result .= '#'.$parsed['fragment'];
        }

        return $result;
    }

    /**
     * Mask a JWT token, preserving the header segment for debugging.
     *
     * Validates that the token has exactly 3 dot-separated segments. If valid,
     * the header (first segment) is preserved and the payload and signature
     * are replaced with "[MASKED]".
     *
     * Example: "eyJhbGci.eyJzdWIi.SflKxwRJ" becomes "eyJhbGci.[MASKED].[MASKED]"
     */
    public static function jwt(string $token): string
    {
        if ($token === '') {
            return '';
        }

        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return self::string($token);
        }

        // Validate each segment is non-empty
        foreach ($parts as $part) {
            if ($part === '') {
                return self::string($token);
            }
        }

        return $parts[0].'.[MASKED].[MASKED]';
    }

    /**
     * Mask a URL component, showing only the first character.
     */
    private static function maskUrlPart(string $part): string
    {
        $length = mb_strlen($part);

        if ($length <= 1) {
            return $part;
        }

        $config = self::config();

        return mb_substr($part, 0, 1).str_repeat($config->maskChar, $length - 1);
    }
}
