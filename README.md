![Factorize](https://user-images.githubusercontent.com/3613731/233783043-44972aad-e550-495f-8a43-45914cd475be.png)

# Magically add missing Model Factories

[![Latest Version on Packagist](https://img.shields.io/packagist/v/smousss/laravel-factorize.svg?style=flat-square)](https://packagist.org/packages/smousss/laravel-factorize)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/smousss/laravel-factorize/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/smousss/laravel-factorize/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/smousss/laravel-factorize.svg?style=flat-square)](https://packagist.org/packages/smousss/laravel-factorize)

Smousss generates missing Model Factories to help you create fake data seeders and write tests at ease. A dream came true for developers inheriting legacy codebases.

```php
namespace Database\Factories;

use …

class ThreadFactory extends Factory
{
    public function definition()
    {
        return [
            'title' => fake()->sentence(),
            'slug' => fake()->slug(),
            'content' => fake()->paragraph(),
        ];
    }

    public function withUser(): static
    {
        return $this->state(function () {
            return [
                'user_id' => User::factory(),
            ];
        });
    }

    …
}
```

## Installation

Install the package via Composer:

```bash
composer require smousss/laravel-factorize
```

Publish the config file:

```bash
php artisan vendor:publish --tag=factorize-config
```

## Usage

1. First, [generate a secret key](https://smousss.com/dashboard) on smousss.com.
2. Add it to your `SMOUSSS_SECRET_KEY` environment variable.
3. Then, create a factory for your Post model: `php artisan smousss:factorize App\\Models\\Post`. Or for multiple models: `php artisan smousss:factorize App\\Models\\Post App\\Models\\Comment`

## Credit

Factorize for Laravel has been developed by [Benjamin Crozat](https://benjamincrozat.com) for [Smousss](https://smousss.com) ([Twitter](https://twitter.com/benjamincrozat)).

## License

[MIT](LICENSE.md)
