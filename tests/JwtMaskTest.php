<?php

declare(strict_types=1);

namespace PhilipRehberger\Mask\Tests;

use PhilipRehberger\Mask\Mask;
use PHPUnit\Framework\TestCase;

class JwtMaskTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Mask::resetConfig();
    }

    public function test_jwt_masks_payload_and_signature_keeps_header(): void
    {
        $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIn0.dozjgNryP4J3jVmNHl0w5N_XgL0n3I9PlFUP0THsR8U';

        $result = Mask::jwt($token);

        $this->assertSame('eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.[MASKED].[MASKED]', $result);
    }

    public function test_jwt_non_jwt_string_returns_masked(): void
    {
        $result = Mask::jwt('this-is-not-a-jwt');

        $this->assertStringContainsString('*', $result);
        $this->assertStringNotContainsString('[MASKED]', $result);
    }

    public function test_jwt_with_two_segments_returns_masked(): void
    {
        $result = Mask::jwt('part1.part2');

        $this->assertStringContainsString('*', $result);
    }

    public function test_jwt_with_four_segments_returns_masked(): void
    {
        $result = Mask::jwt('a.b.c.d');

        $this->assertStringContainsString('*', $result);
    }

    public function test_jwt_with_special_characters_in_payload(): void
    {
        $token = 'eyJhbGciOiJIUzI1NiJ9.eyJkYXRhIjoiw7bDvMOkIn0.abcdef123456';

        $result = Mask::jwt($token);

        $this->assertSame('eyJhbGciOiJIUzI1NiJ9.[MASKED].[MASKED]', $result);
    }

    public function test_jwt_empty_string_returns_empty(): void
    {
        $this->assertSame('', Mask::jwt(''));
    }

    public function test_jwt_with_empty_segment_returns_masked(): void
    {
        $result = Mask::jwt('header..signature');

        $this->assertStringContainsString('*', $result);
        $this->assertStringNotContainsString('[MASKED]', $result);
    }
}
