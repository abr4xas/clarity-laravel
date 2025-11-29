<?php

declare(strict_types=1);

namespace Abr4xas\ClarityLaravel\Tests;

use Abr4xas\ClarityLaravel\ClarityLaravelServiceProvider;
use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    use InteractsWithViews;

    protected function getPackageProviders($app): array
    {
        return [
            ClarityLaravelServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('clarity.id', 'XXXXXX');
        $app['config']->set('clarity.active', true);

        $app['config']->set('view.paths', [
            ...$app['config']->get('view.paths'),
            __DIR__ . '/resources/views',
        ]);
    }
}
