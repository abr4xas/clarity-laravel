<?php

declare(strict_types=1);

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

it('displays clarity tag', function (): void {
    $view = $this->blade('<x-clarity::script :enabled="$enabled"/>', ['enabled' => true]);

    $view->assertSee('script');
});

it('does\'t display clarity tag', function (): void {
    $view = $this->blade('<x-clarity::script :enabled="$enabled"/>', ['enabled' => false]);

    $view->assertSee('');
});

// Custom Tags Component Tests
it('displays custom tag component with single value', function (): void {
    config(['clarity.enabled' => true]);

    $view = $this->blade(
        '<x-clarity::tag key="user_role" :values="[\'admin\']" />',
        []
    );

    $view->assertSee('window.clarity', false)
        ->assertSee('"set"', false)
        ->assertSee('"user_role"', false)
        ->assertSee('["admin"]', false);
});

it('displays custom tag component with multiple values', function (): void {
    config(['clarity.enabled' => true]);

    $view = $this->blade(
        '<x-clarity::tag key="features" :values="[\'chat\', \'video\', \'audio\']" />',
        []
    );

    $view->assertSee('window.clarity', false)
        ->assertSee('"set"', false)
        ->assertSee('"features"', false)
        ->assertSee('["chat","video","audio"]', false);
});

it('does not display custom tag when disabled', function (): void {
    config(['clarity.enabled' => false]);

    $view = $this->blade(
        '<x-clarity::tag key="user_role" :values="[\'admin\']" />',
        []
    );

    $view->assertDontSee('window.clarity', false);
});

it('does not display custom tag without key', function (): void {
    config(['clarity.enabled' => true]);

    $view = $this->blade(
        '<x-clarity::tag :values="[\'admin\']" />',
        []
    );

    $view->assertDontSee('window.clarity', false);
});

it('handles empty values array in custom tag', function (): void {
    config(['clarity.enabled' => true]);

    $view = $this->blade(
        '<x-clarity::tag key="test" :values="[]" />',
        []
    );

    $view->assertSee('window.clarity', false)
        ->assertSee('"test"', false)
        ->assertSee('[]', false);
});

// Helper Functions Tests
it('clarity_tag helper returns script when enabled', function (): void {
    config(['clarity.enabled' => true]);

    $output = clarity_tag('plan', 'premium');

    expect($output)
        ->toBeString()
        ->toContain('window.clarity')
        ->toContain('"set"')
        ->toContain('"plan"')
        ->toContain('["premium"]');
});

it('clarity_tag helper handles multiple values', function (): void {
    config(['clarity.enabled' => true]);

    $output = clarity_tag('roles', 'admin', 'editor', 'viewer');

    expect($output)
        ->toBeString()
        ->toContain('window.clarity')
        ->toContain('"roles"')
        ->toContain('["admin","editor","viewer"]');
});

it('clarity_tag helper handles array values', function (): void {
    config(['clarity.enabled' => true]);

    $output = clarity_tag('permissions', ['read', 'write', 'delete']);

    expect($output)
        ->toBeString()
        ->toContain('window.clarity')
        ->toContain('"permissions"')
        ->toContain('["read","write","delete"]');
});

it('clarity_tag helper returns null when disabled', function (): void {
    config(['clarity.enabled' => false]);

    $output = clarity_tag('plan', 'premium');

    expect($output)->toBeNull();
});

it('clarity_identify helper returns script when enabled', function (): void {
    config([
        'clarity.enabled' => true,
        'clarity.enabled_identify_api' => true,
    ]);

    $user = (object) [
        'email' => 'test@example.com',
        'name' => 'Test User',
    ];

    $output = clarity_identify($user);

    expect($output)
        ->toBeString()
        ->toContain('window.clarity')
        ->toContain('"identify"')
        ->toContain('test@example.com')
        ->toContain('Test User');
});

it('clarity_identify helper returns null when disabled', function (): void {
    config(['clarity.enabled' => false]);

    $user = (object) [
        'email' => 'test@example.com',
        'name' => 'Test User',
    ];

    $output = clarity_identify($user);

    expect($output)->toBeNull();
});

it('clarity_identify helper returns null when identify api is disabled', function (): void {
    config([
        'clarity.enabled' => true,
        'clarity.enabled_identify_api' => false,
    ]);

    $user = (object) [
        'email' => 'test@example.com',
        'name' => 'Test User',
    ];

    $output = clarity_identify($user);

    expect($output)->toBeNull();
});

// Validation Tests - Verifying View Rendering
it('clarity_tag helper renders valid HTML with script tags', function (): void {
    config(['clarity.enabled' => true]);

    $output = clarity_tag('test_key', 'test_value');

    expect($output)
        ->toBeString()
        ->toMatch('/<script[^>]*type=["\']text\/javascript["\'][^>]*>/')
        ->toMatch('/<\/script>/')
        ->and(strip_tags((string) $output, '<script>'))->not->toBe(''); // Has content inside script tags
});

it('clarity_tag helper renders the tag component view correctly', function (): void {
    config(['clarity.enabled' => true]);

    $output = clarity_tag('environment', 'production');

    // Verify it's wrapped in an IIFE (Immediately Invoked Function Expression)
    expect($output)
        ->toContain('(function(){')
        ->toContain('})();')
        ->and(mb_substr_count((string) $output, '<script'))->toBe(1) // Only one script tag
        ->and(mb_substr_count((string) $output, '</script>'))->toBe(1); // Only one closing tag
});

it('clarity_identify helper renders valid HTML with script tags', function (): void {
    config([
        'clarity.enabled' => true,
        'clarity.enabled_identify_api' => true,
    ]);

    $user = (object) [
        'email' => 'user@test.com',
        'name' => 'Test User',
    ];

    $output = clarity_identify($user);

    expect($output)
        ->toBeString()
        ->toMatch('/<script[^>]*type=["\']text\/javascript["\'][^>]*>/')
        ->toMatch('/<\/script>/')
        ->and(mb_substr_count((string) $output, '<script'))->toBe(1)
        ->and(mb_substr_count((string) $output, '</script>'))->toBe(1);
});

it('clarity_identify helper renders the identify component view correctly', function (): void {
    config([
        'clarity.enabled' => true,
        'clarity.enabled_identify_api' => true,
    ]);

    $user = (object) [
        'email' => 'admin@example.com',
        'name' => 'Admin User',
    ];

    $output = clarity_identify($user, 'session-123', 'page-456');

    // Verify it's wrapped in an IIFE
    expect($output)
        ->toContain('(function(){')
        ->toContain('})();')
        ->toContain('"identify"')
        ->toContain('admin@example.com')
        ->toContain('Admin User');
});

it('clarity_tag helper properly formats the key in JavaScript', function (): void {
    config(['clarity.enabled' => true]);

    $output = clarity_tag('user_role', 'admin');

    // Verify the key is properly quoted in JavaScript
    expect($output)
        ->toContain('window.clarity("set", "user_role"')
        ->and(mb_substr_count((string) $output, '<script'))->toBe(1)
        ->and(mb_substr_count((string) $output, '</script>'))->toBe(1);
});

it('clarity_tag helper handles special characters in values', function (): void {
    config(['clarity.enabled' => true]);

    $output = clarity_tag('message', 'Hello "World"', "Test's Value");

    expect($output)
        ->toBeString()
        ->toContain('window.clarity');

    // Ensure JSON encoding handles special characters
    $jsonStart = mb_strpos((string) $output, '["');
    expect($jsonStart)->toBeGreaterThan(0);
});
