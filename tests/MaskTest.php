<?php

declare(strict_types=1);

namespace PhilipRehberger\Mask\Tests;

use PhilipRehberger\Mask\Mask;
use PhilipRehberger\Mask\MaskConfig;
use PHPUnit\Framework\TestCase;

class MaskTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Mask::resetConfig();
    }

    public function test_email_masking_standard_format(): void
    {
        $this->assertSame('j***@e******.com', Mask::email('john@example.com'));
    }

    public function test_email_masking_short_local_part(): void
    {
        $this->assertSame('a@e******.com', Mask::email('a@example.com'));
    }

    public function test_email_masking_long_address(): void
    {
        $result = Mask::email('firstname.lastname@company.org');

        $this->assertStringStartsWith('f', $result);
        $this->assertStringEndsWith('.org', $result);
        $this->assertStringContainsString('@', $result);
    }

    public function test_phone_masking_us_format(): void
    {
        $result = Mask::phone('+1-555-123-4567');

        $this->assertStringEndsWith('4567', $result);
        $this->assertStringStartsWith('+1-55', $result);
        $this->assertStringContainsString('*', $result);
    }

    public function test_phone_masking_international_format(): void
    {
        $result = Mask::phone('+44 20 7946 0958');

        $this->assertStringContainsString('*', $result);
        $this->assertStringEndsWith('0958', $result);
    }

    public function test_credit_card_masking_visa(): void
    {
        $this->assertSame('4111 **** **** 1111', Mask::creditCard('4111 1234 5678 1111'));
    }

    public function test_credit_card_masking_mastercard(): void
    {
        $result = Mask::creditCard('5500-0000-0000-0004');

        $this->assertStringStartsWith('5500', $result);
        $this->assertStringEndsWith('0004', $result);
        $this->assertStringContainsString('*', $result);
    }

    public function test_credit_card_masking_amex(): void
    {
        $result = Mask::creditCard('3782 822463 10005');

        $this->assertStringStartsWith('3782', $result);
        $this->assertStringEndsWith('0005', $result);
    }

    public function test_ip_masking_v4(): void
    {
        $this->assertSame('192.168.*.*', Mask::ip('192.168.1.100'));
    }

    public function test_ip_masking_v6(): void
    {
        $result = Mask::ip('2001:0db8:85a3:0000:0000:8a2e:0370:7334');

        $this->assertStringStartsWith('2001:0db8:', $result);
        $this->assertStringContainsString('*', $result);
    }

    public function test_generic_string_masking_with_defaults(): void
    {
        $this->assertSame('Se*********ta', Mask::string('SensitiveData'));
    }

    public function test_generic_string_masking_with_custom_visible(): void
    {
        $this->assertSame('Sen*******ata', Mask::string('SensitiveData', 3, 3));
    }

    public function test_deep_array_masking_with_nested_keys(): void
    {
        $data = [
            'name' => 'John',
            'email' => 'john@example.com',
            'address' => [
                'street' => '123 Main St',
                'ssn' => '123-45-6789',
            ],
        ];

        $result = Mask::array($data, ['email', 'ssn']);

        $this->assertSame('John', $result['name']);
        $this->assertNotSame('john@example.com', $result['email']);
        $this->assertStringContainsString('*', $result['email']);
        $this->assertSame('123 Main St', $result['address']['street']);
        $this->assertStringContainsString('*', $result['address']['ssn']);
    }

    public function test_json_masking_round_trip(): void
    {
        $json = '{"name":"John","ssn":"123-45-6789","nested":{"secret":"abc123"}}';

        $result = Mask::json($json, ['ssn', 'secret']);
        $decoded = json_decode($result, true);

        $this->assertSame('John', $decoded['name']);
        $this->assertStringContainsString('*', $decoded['ssn']);
        $this->assertStringContainsString('*', $decoded['nested']['secret']);
    }

    public function test_custom_mask_character(): void
    {
        Mask::configure(new MaskConfig(maskChar: '#'));

        $this->assertSame('Se#########ta', Mask::string('SensitiveData'));
    }

    public function test_empty_values_handled_gracefully(): void
    {
        $this->assertSame('', Mask::email(''));
        $this->assertSame('', Mask::phone(''));
        $this->assertSame('', Mask::creditCard(''));
        $this->assertSame('', Mask::ip(''));
        $this->assertSame('', Mask::string(''));
        $this->assertSame('', Mask::json('', ['key']));
    }

    public function test_keys_not_found_in_array_left_untouched(): void
    {
        $data = ['name' => 'John', 'age' => 30];

        $result = Mask::array($data, ['ssn', 'email']);

        $this->assertSame('John', $result['name']);
        $this->assertSame(30, $result['age']);
    }

    public function test_preserves_non_sensitive_keys_in_arrays(): void
    {
        $data = [
            'id' => 42,
            'username' => 'johndoe',
            'password' => 'secret123',
            'active' => true,
        ];

        $result = Mask::array($data, ['password']);

        $this->assertSame(42, $result['id']);
        $this->assertSame('johndoe', $result['username']);
        $this->assertTrue($result['active']);
        $this->assertStringContainsString('*', $result['password']);
    }

    public function test_unicode_string_masking(): void
    {
        $result = Mask::string('Muenchen', 2, 2);

        $this->assertStringStartsWith('Mu', $result);
        $this->assertStringEndsWith('en', $result);
        $this->assertStringContainsString('*', $result);
    }

    public function test_very_short_strings(): void
    {
        $this->assertSame('*', Mask::string('A'));
        $this->assertSame('**', Mask::string('AB'));
    }

    public function test_preserve_length_false_truncates_output(): void
    {
        Mask::configure(new MaskConfig(preserveLength: false));

        $result = Mask::string('SensitiveData');

        $this->assertSame('Se***ta', $result);
        $this->assertSame(7, mb_strlen($result));
    }
}
