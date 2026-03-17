# PHP Mask

[![Tests](https://github.com/philiprehberger/php-mask/actions/workflows/tests.yml/badge.svg)](https://github.com/philiprehberger/php-mask/actions/workflows/tests.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/philiprehberger/php-mask.svg)](https://packagist.org/packages/philiprehberger/php-mask)
[![License](https://img.shields.io/github/license/philiprehberger/php-mask)](LICENSE)

Mask sensitive data in strings, arrays, and objects for safe logging.

---

## Requirements

| Dependency | Version |
|------------|---------|
| PHP        | ^8.2    |

---

## Installation

```bash
composer require philiprehberger/php-mask
```

---

## Usage

### Email masking

```php
use PhilipRehberger\Mask\Mask;

Mask::email('john@example.com');
// "j***@e******.com"
```

### Phone masking

```php
Mask::phone('+1-555-123-4567');
// "+1-555-***-4567"
```

### Credit card masking

```php
Mask::creditCard('4111 1234 5678 1111');
// "4111 **** **** 1111"
```

### IP address masking

```php
Mask::ip('192.168.1.100');
// "192.168.*.*"
```

### Generic string masking

```php
Mask::string('SensitiveData');
// "Se*********ta"

Mask::string('SensitiveData', visibleStart: 3, visibleEnd: 3);
// "Sen*******ata"
```

### Array masking (deep)

```php
$data = [
    'name' => 'John',
    'email' => 'john@example.com',
    'nested' => [
        'ssn' => '123-45-6789',
    ],
];

Mask::array($data, ['email', 'ssn']);
// ['name' => 'John', 'email' => 'jo************om', 'nested' => ['ssn' => '12*******89']]
```

### JSON masking

```php
$json = '{"name":"John","ssn":"123-45-6789"}';

Mask::json($json, ['ssn']);
// '{"name":"John","ssn":"12*******89"}'
```

### Custom configuration

```php
use PhilipRehberger\Mask\MaskConfig;

Mask::configure(new MaskConfig(
    maskChar: '#',
    preserveLength: false,
    visibleStart: 3,
    visibleEnd: 3,
));

Mask::string('SensitiveData');
// "Sen###ata"
```

---

## API

| Method | Description |
|--------|-------------|
| `Mask::email(string $email): string` | Mask an email address, preserving first char of local/domain and TLD |
| `Mask::phone(string $phone): string` | Mask a phone number, preserving country code and last 4 digits |
| `Mask::creditCard(string $number): string` | Mask a credit card, showing first 4 and last 4 digits |
| `Mask::ip(string $ip): string` | Mask an IP address, showing first two octets (v4) or groups (v6) |
| `Mask::string(string $value, int $visibleStart = 2, int $visibleEnd = 2): string` | Mask a generic string with configurable visible characters |
| `Mask::array(array $data, array $keys): array` | Deep-mask specified keys in an associative array |
| `Mask::json(string $json, array $keys): string` | Parse JSON, mask specified keys, re-encode |
| `Mask::configure(MaskConfig $config): void` | Set global masking configuration |
| `Mask::resetConfig(): void` | Reset configuration to defaults |

---

## Development

```bash
composer install
vendor/bin/phpunit
vendor/bin/pint --test
vendor/bin/phpstan analyse
```

## License

MIT
