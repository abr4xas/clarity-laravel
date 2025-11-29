<?php

declare(strict_types=1);

use Illuminate\Support\Facades\View;

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
        if (! config('clarity.enabled', false)) {
            return null;
        }

        $flatValues = [];
        foreach ($values as $value) {
            if (is_array($value)) {
                $flatValues = array_merge($flatValues, $value);
            } else {
                $flatValues[] = $value;
            }
        }

        return View::make('clarity::components.tag', [
            'enabled' => true,
            'key' => $key,
            'values' => $flatValues,
        ])->render();
    }
}

if (! function_exists('clarity_identify')) {
    /**
     * Identify a user for the current Clarity session.
     *
     * @param  object  $user  The user object (should have email and name properties)
     * @param  string|null  $customSessionId  Optional custom session ID
     * @param  string|null  $customPageId  Optional custom page ID
     * @return string|null The rendered script tag or null
     */
    function clarity_identify(object $user, ?string $customSessionId = null, ?string $customPageId = null): ?string
    {
        if (! config('clarity.enabled', false) || ! config('clarity.enabled_identify_api', false)) {
            return null;
        }

        return View::make('clarity::components.identify', [
            'enabled' => true,
            'user' => $user,
            'custom_session_id' => $customSessionId,
            'custom_page_id' => $customPageId,
        ])->render();
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
        if (! config('clarity.enabled', false)) {
            return null;
        }

        return View::make('clarity::components.consent', [
            'enabled' => true,
            'granted' => $granted,
        ])->render();
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
        if (! config('clarity.enabled', false) || ! config('clarity.enabled_identify_api', false)) {
            return null;
        }

        return View::make('clarity::components.custom-user-id', [
            'enabled' => true,
            'user_id' => $userId,
        ])->render();
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
        if (! config('clarity.enabled', false) || ! config('clarity.enabled_identify_api', false)) {
            return null;
        }

        return View::make('clarity::components.custom-session-id', [
            'enabled' => true,
            'session_id' => $sessionId,
        ])->render();
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
        if (! config('clarity.enabled', false) || ! config('clarity.enabled_identify_api', false)) {
            return null;
        }

        return View::make('clarity::components.custom-page-id', [
            'enabled' => true,
            'page_id' => $pageId,
        ])->render();
    }
}
