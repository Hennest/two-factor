# Two-Factor Authentication

This package provides a robust two-factor authentication (2FA) solution for Laravel applications using Google Authenticator.

## Installation

Install the package via Composer:

```bash
composer require hennest/two-factor
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=two-factor-config
```

This will create a `config/two-factor.php` file. Adjust the settings as needed.

## Database Migrations

Publish and run the migrations:

```bash
php artisan vendor:publish --tag=two-factor-migrations
php artisan migrate
```

## Usage

### Setup

1. Add the `HasTwoFactorAuthentication` trait to your User model:

```php
use Hennest\TwoFactor\Traits\HasTwoFactorAuthentication;

class User extends Authenticatable
{
    use HasTwoFactorAuthentication;
    
    // ...
}
```

## Customization

### Views

Publish the views to customize them:

```bash
php artisan vendor:publish --tag=two-factor-views
```

### Translations

Publish the language files to customize messages:

```bash
php artisan vendor:publish --tag=two-factor-lang
```

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This library is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
