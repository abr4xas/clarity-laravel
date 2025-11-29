<?php

declare(strict_types=1);

namespace Abr4xas\ClarityLaravel;

use Abr4xas\ClarityLaravel\Middleware\ClarityMiddleware;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class ClarityLaravelServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('clarity')
            ->hasConfigFile()
            ->hasViews();
    }

    public function packageRegistered(): void
    {
        // Load helper functions
        if (file_exists($helpers = __DIR__.'/helpers.php')) {
            require_once $helpers;
        }
    }

    public function boot(): void
    {
        parent::boot();

        // Register middleware alias for manual registration by the user
        if ($this->app->bound('router')) {
            $this->app['router']->aliasMiddleware('clarity', ClarityMiddleware::class);
        }
    }
}
