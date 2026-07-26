<?php

declare(strict_types=1);

use Illuminate\Support\Facades\View;

if (! function_exists('clarity_render')) {
    /**
     * Guard on Clarity being enabled, then render a Clarity component view.
     *
     * Centralizes the `clarity.enabled` (and optional identify-API) checks so
     * every helper shares a single source of truth for when a script is emitted.
     *
     * @internal Shared implementation for the clarity_* helpers.
     *
     * @param  array<string, mixed>  $data
     */
    function clarity_render(string $component, array $data, bool $requiresIdentifyApi = false): ?string
    {
        if (! config('clarity.enabled', false)) {
            return null;
        }

        if ($requiresIdentifyApi && ! config('clarity.enabled_identify_api', false)) {
            return null;
        }

        return View::make('clarity::components.'.$component, ['enabled' => true] + $data)->render();
    }
}

if (! function_exists('clarity_tag')) {
    /**
     * Set a custom tag for the current Clarity session.
     *
     * @param  string  $key  The key for the custom tag
     * @param  mixed  ...$values  The value(s) for the custom tag (strings or arrays)
     * @return string|null The rendered script tag or null
     */
    function clarity_tag(string $key, mixed ...$values): ?string
    {
        $flatValues = [];
        foreach ($values as $value) {
            if (is_array($value)) {
                $flatValues = array_merge($flatValues, $value);
            } else {
                $flatValues[] = $value;
            }
        }

        return clarity_render('tag', ['key' => $key, 'values' => $flatValues]);
    }
}

if (! function_exists('clarity_identify')) {
    /**
     * Identify a user for the current Clarity session.
     *
     * @param  object  $user  User object exposing nullable `email` and `name` properties
     * @param  string|null  $customSessionId  Optional custom session ID
     * @param  string|null  $customPageId  Optional custom page ID
     * @return string|null The rendered script tag or null
     */
    function clarity_identify(object $user, ?string $customSessionId = null, ?string $customPageId = null): ?string
    {
        return clarity_render('identify', [
            'user' => $user,
            'custom_session_id' => $customSessionId,
            'custom_page_id' => $customPageId,
        ], requiresIdentifyApi: true);
    }
}

if (! function_exists('clarity_consent')) {
    /**
     * Set consent for the current Clarity session.
     *
     * @param  bool  $granted  Whether consent is granted (true) or denied (false)
     * @return string|null The rendered script tag or null
     */
    function clarity_consent(bool $granted = true): ?string
    {
        return clarity_render('consent', ['granted' => $granted]);
    }
}

if (! function_exists('clarity_set_custom_user_id')) {
    /**
     * Set a custom user ID for the current Clarity session.
     * Requires identify API to be enabled.
     *
     * @param  string  $userId  The custom user ID
     * @return string|null The rendered script tag or null
     */
    function clarity_set_custom_user_id(string $userId): ?string
    {
        return clarity_render('custom-user-id', ['user_id' => $userId], requiresIdentifyApi: true);
    }
}

if (! function_exists('clarity_set_custom_session_id')) {
    /**
     * Set a custom session ID for the current Clarity session.
     * Requires identify API to be enabled.
     *
     * @param  string  $sessionId  The custom session ID
     * @return string|null The rendered script tag or null
     */
    function clarity_set_custom_session_id(string $sessionId): ?string
    {
        return clarity_render('custom-session-id', ['session_id' => $sessionId], requiresIdentifyApi: true);
    }
}

if (! function_exists('clarity_set_custom_page_id')) {
    /**
     * Set a custom page ID for the current Clarity session.
     * Requires identify API to be enabled.
     *
     * @param  string  $pageId  The custom page ID
     * @return string|null The rendered script tag or null
     */
    function clarity_set_custom_page_id(string $pageId): ?string
    {
        return clarity_render('custom-page-id', ['page_id' => $pageId], requiresIdentifyApi: true);
    }
}
