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

it("does't display clarity tag", function (): void {
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

// Global Tags Tests
it('displays global tags in script component', function (): void {
    config([
        'clarity.enabled' => true,
        'clarity.global_tags' => [
            'environment' => ['production'],
            'version' => ['1.0.0'],
        ],
    ]);

    $view = $this->blade('<x-clarity::script />', []);

    $view->assertSee('window.clarity', false)
        ->assertSee('"environment"', false)
        ->assertSee('"version"', false)
        ->assertSee('["production"]', false)
        ->assertSee('["1.0.0"]', false);
});

it('does not display global tags when disabled', function (): void {
    config([
        'clarity.enabled' => false,
        'clarity.global_tags' => ['test' => ['value']],
    ]);

    $view = $this->blade('<x-clarity::script />', []);

    $view->assertDontSee('"test"', false);
});

it('handles empty global tags', function (): void {
    config([
        'clarity.enabled' => true,
        'clarity.global_tags' => [],
    ]);

    $view = $this->blade('<x-clarity::script />', []);

    $view->assertSee('script', false)
        ->assertDontSee('setGlobalTags', false);
});

// Consent API Tests
it('displays consent component with v2 granted', function (): void {
    config([
        'clarity.enabled' => true,
        'clarity.consent_version' => 'v2',
    ]);

    $view = $this->blade('<x-clarity::consent :granted="true" />', []);

    $view->assertSee('window.clarity', false)
        ->assertSee('"consent"', false)
        ->assertSee('"granted"', false);
});

it('displays consent component with v2 denied', function (): void {
    config([
        'clarity.enabled' => true,
        'clarity.consent_version' => 'v2',
    ]);

    $view = $this->blade('<x-clarity::consent :granted="false" />', []);

    $view->assertSee('window.clarity', false)
        ->assertSee('"consent"', false)
        ->assertSee('"denied"', false);
});

it('displays consent component with v1', function (): void {
    config([
        'clarity.enabled' => true,
        'clarity.consent_version' => 'v1',
    ]);

    $view = $this->blade('<x-clarity::consent :granted="true" />', []);

    $view->assertSee('window.clarity', false)
        ->assertSee('"consent"', false)
        ->assertSee('true', false);
});

it('clarity_consent helper returns script when enabled', function (): void {
    config(['clarity.enabled' => true]);

    $output = clarity_consent(true);

    expect($output)
        ->toBeString()
        ->toContain('window.clarity')
        ->toContain('"consent"');
});

it('clarity_consent helper returns null when disabled', function (): void {
    config(['clarity.enabled' => false]);

    $output = clarity_consent(true);

    expect($output)->toBeNull();
});

it('consent component does not display when disabled', function (): void {
    config(['clarity.enabled' => false]);

    $view = $this->blade('<x-clarity::consent :granted="true" />', []);

    $view->assertDontSee('window.clarity', false);
});

it('consent component handles enabled attribute', function (): void {
    config(['clarity.enabled' => true]);

    $view = $this->blade('<x-clarity::consent :granted="true" :enabled="false" />', []);

    $view->assertDontSee('window.clarity', false);
});

// Custom ID Helpers Tests
it('clarity_set_custom_user_id helper returns script when enabled', function (): void {
    config([
        'clarity.enabled' => true,
        'clarity.enabled_identify_api' => true,
    ]);

    $output = clarity_set_custom_user_id('user-123');

    expect($output)
        ->toBeString()
        ->toContain('window.clarity')
        ->toContain('"identify"')
        ->toContain('user-123');
});

it('clarity_set_custom_user_id helper returns null when disabled', function (): void {
    config(['clarity.enabled' => false]);

    $output = clarity_set_custom_user_id('user-123');

    expect($output)->toBeNull();
});

it('clarity_set_custom_user_id helper returns null when identify api is disabled', function (): void {
    config([
        'clarity.enabled' => true,
        'clarity.enabled_identify_api' => false,
    ]);

    $output = clarity_set_custom_user_id('user-123');

    expect($output)->toBeNull();
});

it('clarity_set_custom_session_id helper returns script when enabled', function (): void {
    config([
        'clarity.enabled' => true,
        'clarity.enabled_identify_api' => true,
    ]);

    $output = clarity_set_custom_session_id('session-456');

    expect($output)
        ->toBeString()
        ->toContain('window.clarity')
        ->toContain('"identify"')
        ->toContain('session-456');
});

it('clarity_set_custom_session_id helper returns null when disabled', function (): void {
    config(['clarity.enabled' => false]);

    $output = clarity_set_custom_session_id('session-456');

    expect($output)->toBeNull();
});

it('clarity_set_custom_session_id helper returns null when identify api is disabled', function (): void {
    config([
        'clarity.enabled' => true,
        'clarity.enabled_identify_api' => false,
    ]);

    $output = clarity_set_custom_session_id('session-456');

    expect($output)->toBeNull();
});

it('clarity_set_custom_page_id helper returns script when enabled', function (): void {
    config([
        'clarity.enabled' => true,
        'clarity.enabled_identify_api' => true,
    ]);

    $output = clarity_set_custom_page_id('page-789');

    expect($output)
        ->toBeString()
        ->toContain('window.clarity')
        ->toContain('"identify"')
        ->toContain('page-789');
});

it('clarity_set_custom_page_id helper returns null when disabled', function (): void {
    config(['clarity.enabled' => false]);

    $output = clarity_set_custom_page_id('page-789');

    expect($output)->toBeNull();
});

it('clarity_set_custom_page_id helper returns null when identify api is disabled', function (): void {
    config([
        'clarity.enabled' => true,
        'clarity.enabled_identify_api' => false,
    ]);

    $output = clarity_set_custom_page_id('page-789');

    expect($output)->toBeNull();
});

// Custom ID Components Tests
it('displays custom user id component', function (): void {
    config([
        'clarity.enabled' => true,
        'clarity.enabled_identify_api' => true,
    ]);

    $view = $this->blade('<x-clarity::custom-user-id user_id="user-123" />', []);

    $view->assertSee('window.clarity', false)
        ->assertSee('"identify"', false)
        ->assertSee('user-123', false);
});

it('does not display custom user id component when identify api is disabled', function (): void {
    config([
        'clarity.enabled' => true,
        'clarity.enabled_identify_api' => false,
    ]);

    $view = $this->blade('<x-clarity::custom-user-id user_id="user-123" />', []);

    $view->assertDontSee('window.clarity', false);
});

it('does not display custom user id component when disabled', function (): void {
    config(['clarity.enabled' => false]);

    $view = $this->blade('<x-clarity::custom-user-id user_id="user-123" />', []);

    $view->assertDontSee('window.clarity', false);
});

it('custom user id component handles enabled attribute', function (): void {
    config([
        'clarity.enabled' => true,
        'clarity.enabled_identify_api' => true,
    ]);

    $view = $this->blade('<x-clarity::custom-user-id user_id="user-123" :enabled="false" />', []);

    $view->assertDontSee('window.clarity', false);
});

it('custom user id component does not display without user_id', function (): void {
    config([
        'clarity.enabled' => true,
        'clarity.enabled_identify_api' => true,
    ]);

    $view = $this->blade('<x-clarity::custom-user-id />', []);

    $view->assertDontSee('window.clarity', false);
});

it('displays custom session id component', function (): void {
    config([
        'clarity.enabled' => true,
        'clarity.enabled_identify_api' => true,
    ]);

    $view = $this->blade('<x-clarity::custom-session-id session_id="session-456" />', []);

    $view->assertSee('window.clarity', false)
        ->assertSee('"identify"', false)
        ->assertSee('session-456', false);
});

it('does not display custom session id component when identify api is disabled', function (): void {
    config([
        'clarity.enabled' => true,
        'clarity.enabled_identify_api' => false,
    ]);

    $view = $this->blade('<x-clarity::custom-session-id session_id="session-456" />', []);

    $view->assertDontSee('window.clarity', false);
});

it('does not display custom session id component when disabled', function (): void {
    config(['clarity.enabled' => false]);

    $view = $this->blade('<x-clarity::custom-session-id session_id="session-456" />', []);

    $view->assertDontSee('window.clarity', false);
});

it('custom session id component handles enabled attribute', function (): void {
    config([
        'clarity.enabled' => true,
        'clarity.enabled_identify_api' => true,
    ]);

    $view = $this->blade('<x-clarity::custom-session-id session_id="session-456" :enabled="false" />', []);

    $view->assertDontSee('window.clarity', false);
});

it('custom session id component does not display without session_id', function (): void {
    config([
        'clarity.enabled' => true,
        'clarity.enabled_identify_api' => true,
    ]);

    $view = $this->blade('<x-clarity::custom-session-id />', []);

    $view->assertDontSee('window.clarity', false);
});

it('displays custom page id component', function (): void {
    config([
        'clarity.enabled' => true,
        'clarity.enabled_identify_api' => true,
    ]);

    $view = $this->blade('<x-clarity::custom-page-id page_id="page-789" />', []);

    $view->assertSee('window.clarity', false)
        ->assertSee('"identify"', false)
        ->assertSee('page-789', false);
});

it('does not display custom page id component when identify api is disabled', function (): void {
    config([
        'clarity.enabled' => true,
        'clarity.enabled_identify_api' => false,
    ]);

    $view = $this->blade('<x-clarity::custom-page-id page_id="page-789" />', []);

    $view->assertDontSee('window.clarity', false);
});

it('does not display custom page id component when disabled', function (): void {
    config(['clarity.enabled' => false]);

    $view = $this->blade('<x-clarity::custom-page-id page_id="page-789" />', []);

    $view->assertDontSee('window.clarity', false);
});

it('custom page id component handles enabled attribute', function (): void {
    config([
        'clarity.enabled' => true,
        'clarity.enabled_identify_api' => true,
    ]);

    $view = $this->blade('<x-clarity::custom-page-id page_id="page-789" :enabled="false" />', []);

    $view->assertDontSee('window.clarity', false);
});

it('custom page id component does not display without page_id', function (): void {
    config([
        'clarity.enabled' => true,
        'clarity.enabled_identify_api' => true,
    ]);

    $view = $this->blade('<x-clarity::custom-page-id />', []);

    $view->assertDontSee('window.clarity', false);
});

// Queue System Tests
it('tag component includes queue functionality', function (): void {
    config(['clarity.enabled' => true]);

    $view = $this->blade('<x-clarity::tag key="test" :values="[\'value\']" />', []);

    $view->assertSee('window.clarity', false)
        ->assertSee('_clarityQueue', false);
});

it('queue component renders correctly', function (): void {
    config(['clarity.enabled' => true]);

    $view = $this->blade('<x-clarity::queue />', []);

    $view->assertSee('_clarityQueue', false)
        ->assertSee('processQueue', false);
});

it('queue component does not display when disabled', function (): void {
    config(['clarity.enabled' => false]);

    $view = $this->blade('<x-clarity::queue />', []);

    $view->assertDontSee('_clarityQueue', false);
});

it('queue component handles enabled attribute', function (): void {
    config(['clarity.enabled' => true]);

    $view = $this->blade('<x-clarity::queue :enabled="false" />', []);

    $view->assertDontSee('_clarityQueue', false);
});

it('queue component processes queued items correctly', function (): void {
    config(['clarity.enabled' => true]);

    $view = $this->blade('<x-clarity::queue />', []);

    $view->assertSee('_clarityQueue', false)
        ->assertSee('processQueue', false)
        ->assertSee('window.clarity', false)
        ->assertSee('method', false);
});
