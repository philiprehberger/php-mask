<?php

declare(strict_types=1);

namespace PhilipRehberger\Mask\Tests;

use PhilipRehberger\Mask\Mask;
use PhilipRehberger\Mask\RedactionPolicy;
use PHPUnit\Framework\TestCase;

class RedactionPolicyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Mask::resetConfig();
    }

    public function test_mask_field_with_email_method(): void
    {
        $policy = RedactionPolicy::create()
            ->maskField('email', 'email');

        $data = ['name' => 'John', 'email' => 'john@example.com'];
        $result = $policy->apply($data);

        $this->assertSame('John', $result['name']);
        $this->assertSame('j***@e******.com', $result['email']);
    }

    public function test_mask_field_with_full_method(): void
    {
        $policy = RedactionPolicy::create()
            ->maskField('secret', 'full');

        $data = ['secret' => 'top-secret-value'];
        $result = $policy->apply($data);

        $this->assertSame(str_repeat('*', mb_strlen('top-secret-value')), $result['secret']);
        $this->assertStringNotContainsString('top', $result['secret']);
    }

    public function test_mask_field_with_partial_method(): void
    {
        $policy = RedactionPolicy::create()
            ->maskField('token', 'partial');

        $data = ['token' => 'abc123xyz'];
        $result = $policy->apply($data);

        $this->assertStringStartsWith('ab', $result['token']);
        $this->assertStringEndsWith('yz', $result['token']);
        $this->assertStringContainsString('*', $result['token']);
    }

    public function test_mask_pattern_matches_regex_against_field_names(): void
    {
        $policy = RedactionPolicy::create()
            ->maskPattern('/secret|password/i', 'full');

        $data = [
            'name' => 'John',
            'password' => 'hunter2',
            'api_secret' => 'sk-12345',
        ];
        $result = $policy->apply($data);

        $this->assertSame('John', $result['name']);
        $this->assertSame(str_repeat('*', 7), $result['password']);
        $this->assertSame(str_repeat('*', 8), $result['api_secret']);
    }

    public function test_apply_processes_nested_arrays_with_dot_notation(): void
    {
        $policy = RedactionPolicy::create()
            ->maskField('user.profile.email', 'email');

        $data = [
            'user' => [
                'name' => 'John',
                'profile' => [
                    'email' => 'john@example.com',
                    'bio' => 'Hello world',
                ],
            ],
        ];
        $result = $policy->apply($data);

        $this->assertSame('John', $result['user']['name']);
        $this->assertSame('j***@e******.com', $result['user']['profile']['email']);
        $this->assertSame('Hello world', $result['user']['profile']['bio']);
    }

    public function test_wildcard_paths(): void
    {
        $policy = RedactionPolicy::create()
            ->maskField('*.secret', 'full');

        $data = [
            'service_a' => [
                'name' => 'Service A',
                'secret' => 'key-aaa',
            ],
            'service_b' => [
                'name' => 'Service B',
                'secret' => 'key-bbb',
            ],
        ];
        $result = $policy->apply($data);

        $this->assertSame('Service A', $result['service_a']['name']);
        $this->assertSame(str_repeat('*', 7), $result['service_a']['secret']);
        $this->assertSame('Service B', $result['service_b']['name']);
        $this->assertSame(str_repeat('*', 7), $result['service_b']['secret']);
    }

    public function test_merge_combines_two_policies(): void
    {
        $policyA = RedactionPolicy::create()
            ->maskField('email', 'email');

        $policyB = RedactionPolicy::create()
            ->maskField('phone', 'phone');

        $policyA->merge($policyB);

        $data = [
            'name' => 'John',
            'email' => 'john@example.com',
            'phone' => '+1-555-123-4567',
        ];
        $result = $policyA->apply($data);

        $this->assertSame('John', $result['name']);
        $this->assertSame('j***@e******.com', $result['email']);
        $this->assertStringContainsString('*', $result['phone']);
        $this->assertStringEndsWith('4567', $result['phone']);
    }

    public function test_mask_field_with_card_method(): void
    {
        $policy = RedactionPolicy::create()
            ->maskField('card_number', 'card');

        $data = ['card_number' => '4111 1234 5678 1111'];
        $result = $policy->apply($data);

        $this->assertSame('4111 **** **** 1111', $result['card_number']);
    }

    public function test_mask_field_with_ip_method(): void
    {
        $policy = RedactionPolicy::create()
            ->maskField('ip_address', 'ip');

        $data = ['ip_address' => '192.168.1.100'];
        $result = $policy->apply($data);

        $this->assertSame('192.168.*.*', $result['ip_address']);
    }

    public function test_missing_field_path_leaves_data_unchanged(): void
    {
        $policy = RedactionPolicy::create()
            ->maskField('nonexistent.path', 'full');

        $data = ['name' => 'John'];
        $result = $policy->apply($data);

        $this->assertSame('John', $result['name']);
    }

    public function test_pattern_applies_recursively_to_nested_data(): void
    {
        $policy = RedactionPolicy::create()
            ->maskPattern('/^token$/i', 'full');

        $data = [
            'token' => 'root-token',
            'nested' => [
                'token' => 'nested-token',
                'name' => 'test',
            ],
        ];
        $result = $policy->apply($data);

        $this->assertSame(str_repeat('*', 10), $result['token']);
        $this->assertSame(str_repeat('*', 12), $result['nested']['token']);
        $this->assertSame('test', $result['nested']['name']);
    }
}
