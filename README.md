# PHP Mask

[![Tests](https://github.com/philiprehberger/php-mask/actions/workflows/tests.yml/badge.svg)](https://github.com/philiprehberger/php-mask/actions/workflows/tests.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/philiprehberger/php-mask.svg)](https://packagist.org/packages/philiprehberger/php-mask)
[![License](https://img.shields.io/github/license/philiprehberger/php-mask)](LICENSE)

Mask sensitive data in strings, arrays, and objects for safe logging.


## Requirements

- PHP 8.2+


## Installation

```bash
composer require philiprehberger/php-mask
```


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

### IBAN masking

```php
Mask::iban('DE89370400440532013000');
// "DE****************3000"

Mask::iban('GB29NWBK60161331926789', '#');
// "GB################6789"
```

### Custom masking

```php
Mask::custom('SensitiveData', visibleStart: 3, visibleEnd: 3);
// "Sen*******ata"

Mask::custom('SensitiveData', visibleStart: 0, visibleEnd: 0);
// "*************"
```

### Generic string masking

```php
Mask::string('SensitiveData');
// "Se*********ta"

Mask::string('SensitiveData', visibleStart: 3, visibleEnd: 3);
// "Sen*******ata"
```

### Recursive array masking with dot notation

```php
$data = [
    'user' => [
        'name' => 'John',
        'address' => [
            'street' => '123 Main St',
        ],
    ],
    'password' => 'secret123',
];

Mask::arrayRecursive($data, ['password', 'user.address.street']);
// ['user' => ['name' => 'John', 'address' => ['street' => '12*******St']], 'password' => 'se*****23']
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


## API

| Method | Description |
|--------|-------------|
| `Mask::email(string $email): string` | Mask an email address, preserving first char of local/domain and TLD |
| `Mask::phone(string $phone): string` | Mask a phone number, preserving country code and last 4 digits |
| `Mask::creditCard(string $number): string` | Mask a credit card, showing first 4 and last 4 digits |
| `Mask::ip(string $ip): string` | Mask an IP address, showing first two octets (v4) or groups (v6) |
| `Mask::iban(string $value, string $char = '*'): string` | Mask an IBAN, showing country code and last 4 characters |
| `Mask::custom(string $value, int $visibleStart, int $visibleEnd, string $char = '*'): string` | Mask a string with configurable visible start/end lengths |
| `Mask::string(string $value, int $visibleStart = 2, int $visibleEnd = 2): string` | Mask a generic string with configurable visible characters |
| `Mask::arrayRecursive(array $data, array $keys, string $char = '*'): array` | Recursively mask values at specified keys, supporting dot notation paths |
| `Mask::array(array $data, array $keys): array` | Deep-mask specified keys in an associative array |
| `Mask::json(string $json, array $keys): string` | Parse JSON, mask specified keys, re-encode |
| `Mask::configure(MaskConfig $config): void` | Set global masking configuration |
| `Mask::resetConfig(): void` | Reset configuration to defaults |


## Development

```bash
composer install
vendor/bin/phpunit
vendor/bin/pint --test
vendor/bin/phpstan analyse
```

## License

MIT
