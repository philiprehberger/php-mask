<?php

declare(strict_types=1);

namespace PhilipRehberger\Mask\Tests;

use PhilipRehberger\Mask\Mask;
use PHPUnit\Framework\TestCase;

class UrlMaskTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Mask::resetConfig();
    }

    public function test_url_masks_simple_query_params(): void
    {
        $result = Mask::url('https://example.com/path?token=abc123');

        $this->assertStringContainsString('example.com', $result);
        $this->assertStringContainsString('/path', $result);
        $this->assertStringContainsString('token=a', $result);
        $this->assertStringContainsString('*', $result);
        $this->assertStringNotContainsString('abc123', $result);
    }

    public function test_url_masks_embedded_credentials(): void
    {
        $result = Mask::url('https://user:pass@example.com/path');

        $this->assertStringContainsString('example.com', $result);
        $this->assertStringContainsString('u***', $result);
        $this->assertStringContainsString('p***', $result);
        $this->assertStringContainsString('@', $result);
        $this->assertStringNotContainsString('user:', $result);
    }

    public function test_url_without_query_params_preserves_domain(): void
    {
        $result = Mask::url('https://example.com/path/to/resource');

        $this->assertStringContainsString('example.com', $result);
        $this->assertStringContainsString('/path/to/resource', $result);
    }

    public function test_url_with_multiple_query_params(): void
    {
        $result = Mask::url('https://example.com?key=value1&secret=value2&id=12345');

        $this->assertStringContainsString('example.com', $result);
        $this->assertStringContainsString('key=v', $result);
        $this->assertStringContainsString('secret=v', $result);
        $this->assertStringContainsString('id=1', $result);
        $this->assertStringNotContainsString('value1', $result);
        $this->assertStringNotContainsString('value2', $result);
    }

    public function test_invalid_url_returns_masked_string(): void
    {
        $result = Mask::url('not-a-valid-url');

        $this->assertStringContainsString('*', $result);
    }

    public function test_empty_url_returns_empty(): void
    {
        $this->assertSame('', Mask::url(''));
    }

    public function test_url_preserves_port(): void
    {
        $result = Mask::url('https://example.com:8080/path?key=value');

        $this->assertStringContainsString(':8080', $result);
        $this->assertStringContainsString('example.com', $result);
    }

    public function test_url_preserves_fragment(): void
    {
        $result = Mask::url('https://example.com/path#section');

        $this->assertStringContainsString('#section', $result);
    }
}
