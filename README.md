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
    'enabled_identify_api' => env('CLARITY_IDENTIFICATION_ENABLED', false),
    'global_tags' => [],
    'consent_version' => env('CLARITY_CONSENT_VERSION', 'v2'),
    'consent_required' => env('CLARITY_CONSENT_REQUIRED', false),
    'auto_tag_environment' => env('CLARITY_AUTO_TAG_ENV', true),
    'auto_tag_routes' => env('CLARITY_AUTO_TAG_ROUTES', false),
    'auto_identify_users' => env('CLARITY_AUTO_IDENTIFY', false),
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

### Global Tags

Global tags are automatically applied to all Clarity sessions. Define them in your `config/clarity.php` file:

```php
'global_tags' => [
    'environment' => [env('APP_ENV', 'production')],
    'version' => ['1.0.0'],
    'region' => ['us-east'],
],
```

These tags will be automatically included when the Clarity script loads, making it easy to filter sessions by environment, version, or any other global attribute.

### Consent API

Microsoft Clarity supports [Cookie Consent APIs](https://learn.microsoft.com/en-us/clarity/setup-and-installation/clarity-consent-api-v2) for GDPR/CCPA compliance. This package supports both Consent V1 and V2.

#### Configuration

```php
// In config/clarity.php or .env
'consent_version' => env('CLARITY_CONSENT_VERSION', 'v2'), // 'v1' or 'v2'
'consent_required' => env('CLARITY_CONSENT_REQUIRED', false),
```

#### Using the Blade Component

```html
<!-- Grant consent (V2 - recommended) -->
<x-clarity::consent :granted="true" />

<!-- Deny consent (V2) -->
<x-clarity::consent :granted="false" />
```

#### Using the Helper Function

```php
// Grant consent
clarity_consent(true);

// Deny consent
clarity_consent(false);
```

> [!IMPORTANT]
> Consent V2 is recommended and will be enforced for EEA, UK, and Switzerland users starting October 31, 2025.

### Auto-Tagging Middleware

The package includes middleware that automatically tags sessions with useful information:

- **Environment tagging**: Automatically tags sessions with the current environment (local, staging, production)
- **Route tagging**: Optionally tags sessions with route names and prefixes
- **Auto-identification**: Automatically identifies authenticated users

#### Registering the Middleware

**For Laravel 11+:**

You need to manually register the middleware in your `bootstrap/app.php` file. Add it to the `web` middleware group:

```php
// bootstrap/app.php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \Abr4xas\ClarityLaravel\Middleware\ClarityMiddleware::class,
            // or use the alias:
            // 'clarity',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
```

**For Laravel 10 and below:**

You need to manually register the middleware in your `app/Http/Kernel.php` file. Add it to the `web` middleware group:

```php
// app/Http/Kernel.php

protected $middlewareGroups = [
    'web' => [
        // ... other middleware
        \Abr4xas\ClarityLaravel\Middleware\ClarityMiddleware::class,
        // or use the alias:
        // 'clarity',
    ],
];
```

**Alternatively, you can apply it to specific routes or route groups:**

```php
// In your routes file
Route::middleware('clarity')->group(function () {
    // Your routes
});
```

#### Configuration

Configure the middleware behavior in `config/clarity.php`:

```php
'auto_tag_environment' => env('CLARITY_AUTO_TAG_ENV', true),
'auto_tag_routes' => env('CLARITY_AUTO_TAG_ROUTES', false),
'auto_identify_users' => env('CLARITY_AUTO_IDENTIFY', false),
```

When enabled, these tags and identifications are automatically included in your Clarity sessions without any additional code.

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

### Custom IDs

You can set custom user IDs, session IDs, and page IDs independently using dedicated helpers or components. **Note:** These require the Identify API to be enabled (`CLARITY_IDENTIFICATION_ENABLED=true`).

#### Using Helper Functions

```php
// Set custom user ID
clarity_set_custom_user_id('user-123');

// Set custom session ID
clarity_set_custom_session_id('session-456');

// Set custom page ID
clarity_set_custom_page_id('page-789');
```

#### Using Blade Components

```html
<x-clarity::custom-user-id user_id="user-123" />
<x-clarity::custom-session-id session_id="session-456" />
<x-clarity::custom-page-id page_id="page-789" />
```

### Queue System

The package includes a queue system that ensures tags and other Clarity API calls are executed even if Clarity hasn't fully loaded yet. This is handled automatically - tags are queued and processed when Clarity becomes available.

You can also manually include the queue component if needed:

```html
<x-clarity::queue />
```

## Available Helper Functions

This package provides convenient helper functions for use anywhere in your Laravel application:

- `clarity_tag(string $key, mixed ...$values): ?string` - Set custom tags for session filtering
- `clarity_identify(object $user, ?string $customSessionId = null, ?string $customPageId = null): ?string` - Identify a user in the current session (requires Identify API enabled)
- `clarity_consent(bool $granted = true): ?string` - Set consent for the current session
- `clarity_set_custom_user_id(string $userId): ?string` - Set a custom user ID (requires Identify API enabled)
- `clarity_set_custom_session_id(string $sessionId): ?string` - Set a custom session ID (requires Identify API enabled)
- `clarity_set_custom_page_id(string $pageId): ?string` - Set a custom page ID (requires Identify API enabled)

These helpers respect your configuration settings and will return `null` when Clarity is disabled.

## Advanced Usage Examples

### Complete Setup with Auto-Tagging

```php
// config/clarity.php
return [
    'id' => env('CLARITY_ID'),
    'enabled' => env('CLARITY_ENABLED', true),
    'enabled_identify_api' => true,
    'auto_tag_environment' => true,
    'auto_tag_routes' => true,
    'auto_identify_users' => true,
    'global_tags' => [
        'app_version' => [config('app.version')],
        'deployment' => [env('DEPLOYMENT_ID')],
    ],
];
```

### Conditional Consent Handling

```php
// In your cookie consent handler
if ($userConsented) {
    echo clarity_consent(true);
} else {
    echo clarity_consent(false);
}
```

### Dynamic Tagging Based on User Actions

```php
// In a controller after user action
public function upgradePlan(Request $request)
{
    // ... upgrade logic ...

    // Tag the session for analysis
    clarity_tag('plan_upgrade', [
        'from' => $request->user()->plan,
        'to' => $request->input('new_plan'),
        'timestamp' => now()->toIso8601String(),
    ]);

    return redirect()->route('dashboard');
}
```

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
