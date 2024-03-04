<?php

namespace Abr4xas\ClarityLaravel;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ClarityLaravelServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('clarity')
            ->hasConfigFile()
            ->hasViews();
    }
}
