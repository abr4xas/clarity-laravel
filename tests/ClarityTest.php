<?php

use function Pest\Laravel\artisan;

it('can publish config file', function (): void {
    artisan('vendor:publish --tag=clarity-config')
        ->assertExitCode(0);

    expect(file_exists(config_path('clarity.php')))
        ->toBeTrue()
        ->and(unlink(config_path('clarity.php')))
        ->toBe(true);
});

it('can publish view file', function (): void {
    artisan('vendor:publish --tag=clarity-views')
        ->assertExitCode(0);

    expect(file_exists(resource_path('views/vendor/clarity/components/script.blade.php')))
        ->toBeTrue()
        ->and(unlink(resource_path('views/vendor/clarity/components/script.blade.php')))
        ->toBe(true);
});

it('displays clarity tag', function () {
    $view = $this->blade('<x-clarity::script :enabled="$enabled"/>', ['enabled' => true]);

    $view->assertSee('script');
});

it('does\'t display clarity tag', function () {
    $view = $this->blade('<x-clarity::script :enabled="$enabled"/>', ['enabled' => false]);

    $view->assertSee('');
});
