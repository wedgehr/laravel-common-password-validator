# laravel-common-password-validator
Laravel Common Password Validator

An optimized and secure validator to check if a given password is too common.

By default, we ensure password is at least 8 characters, and not one of the 10,000 most common.

## Installation
Require the validator:
> composer require wedgehr/laravel-common-password-validator

Publish the Migration:
> php artisan vendor:publish --provider="Wedge\Validators\CommonPassword\ServiceProvider" --tag=migrations

Optionally publish the config file:
> php artisan vendor:publish --provider="Wedge\Validators\CommonPassword\ServiceProvider" --tag=config

Seed the common passwords:
> php artisan common-password:seed

## Usage
This package installs a custom validator `common_pwd` which can be used in any Validator.

Additionally, you can manually validate a password as such:
```php
Wedge\Validators\CommonPassword\Facade::isCommonPassword('password');
```
