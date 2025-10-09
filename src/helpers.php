<?php

declare(strict_types=1);

use Illuminate\Support\Facades\View;

if (! function_exists('clarity_tag')) {
    /**
     * Set a custom tag for the current Clarity session.
     *
     * @param  string  $key  The key for the custom tag
     * @param  string|array  ...$values  The value(s) for the custom tag
     * @return string|null The rendered script tag or null
     */
    function clarity_tag(string $key, string|array ...$values): ?string
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
