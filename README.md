# Microsoft Clarity for Laravel.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/abr4xas/clarity-laravel.svg?style=flat-square)](https://packagist.org/packages/abr4xas/clarity-laravel)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/abr4xas/clarity-laravel/run-tests.yml?branch=master&label=tests&style=flat-square)](https://github.com/abr4xas/clarity-laravel/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/abr4xas/clarity-laravel/fix-php-code-style-issues.yml?branch=master&label=code%20style&style=flat-square)](https://github.com/abr4xas/clarity-laravel/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/abr4xas/clarity-laravel.svg?style=flat-square)](https://packagist.org/packages/abr4xas/clarity-laravel)

Easy integration of Microsoft Clarity into your Laravel application.

## Installation

You can install the package via composer:

```bash
composer require abr4xas/clarity-laravel
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="clarity-config"
```

This is the contents of the published config file:

```php
<?php

return [
    'id' => env('CLARITY_ID', 'XXXXXX'),
    'enabled' => env('CLARITY_ENABLED', true),
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="clarity-views"
```

## Usage

### General Tracking

- Create an account: The first thing you need to do is create an account on Microsoft Clarity. You can sign up on their website and follow the steps to create your account. Then, get your tracking code and that's it.
- Simply add the blade components to your base layout files.

The `enabled` attribute is *optional*, but can be used to control the tags integration from blade files that extend the base layout. It accepts `true/false`.
This can still be controlled globally via the `.env` file should you need to disable the integration global on different environments as well.

```html
<!-- Should be placed in the head -->
<x-clarity::script :enabled="$enabled" />
```

### Custom Tags API

Microsoft Clarity's [Custom Tags API](https://learn.microsoft.com/en-us/clarity/setup-and-installation/custom-tags) allows you to segment and filter sessions on the Clarity dashboard using custom metadata. This is useful for tracking user roles, subscription plans, feature flags, A/B test variants, and more.

#### Using the Blade Component

```html
<!-- Set a single value -->
<x-clarity::tag key="plan" :values="['premium']" />

<!-- Set multiple values -->
<x-clarity::tag key="features" :values="['chat', 'video', 'notifications']" />

<!-- Dynamic values -->
<x-clarity::tag key="user_role" :values="[auth()->user()->role]" />
```

#### Using the Helper Function

For more programmatic use cases, you can use the `clarity_tag()` helper function anywhere in your application:

```php
// In a controller
clarity_tag('subscription', 'pro');

// Multiple values
clarity_tag('permissions', 'read', 'write', 'admin');

// Array of values
clarity_tag('features', ['feature_a', 'feature_b']);

// In middleware
class TrackUserSegment
{
    public function handle($request, Closure $next)
    {
        if (auth()->check()) {
            clarity_tag('user_segment', auth()->user()->segment);
        }
        return $next($request);
    }
}
```

The helper will return the rendered script tag which you can echo in your views, or it will return `null` if Clarity is disabled.

### Identify API

To implement the [Identify API](https://learn.microsoft.com/en-us/clarity/setup-and-installation/identify-api), use `identify` component.
Set `CLARITY_IDENTIFICATION_ENABLED` value to `true` on the env file.

#### Attributes:
* `user` attribute is *required* accepts the User Model instance or any object. The `email` and `name` attributes are used.
* `enabled` attribute is *optional*.
* `custom_session_id` attribute is *optional*.
* `custom_page_id` attribute is *optional*.

```html
@auth
    <x-clarity::identify :user="request()->user()"/>
@endauth
```
This will compile as:
```js
window.clarity("identify", "user@example.com", null, null, "Username")
```

#### Using the Helper Function

You can also use the `clarity_identify()` helper for programmatic identification:

```php
// Basic usage
clarity_identify(auth()->user());

// With custom session and page IDs
clarity_identify(
    user: auth()->user(),
    customSessionId: 'custom-session-123',
    customPageId: 'checkout-page'
);
```

## Available Helper Functions

This package provides convenient helper functions for use anywhere in your Laravel application:

- `clarity_tag(string $key, string|array ...$values): ?string` - Set custom tags for session filtering
- `clarity_identify(object $user, ?string $customSessionId = null, ?string $customPageId = null): ?string` - Identify a user in the current session

These helpers respect your configuration settings and will return `null` when Clarity is disabled.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Angel](https://github.com/abr4xas)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.


This package is strongly inspired by Google Tag Manager for Laravel created by @awcodes.
