<?php

declare(strict_types=1);

namespace Abr4xas\ClarityLaravel;

/**
 * A single Clarity API call, normalized into the argument list for
 * `window.clarity(...)`. Pure value object: it never reads config — callers
 * pass every input explicitly, so the argument shaping is testable in isolation.
 */
final readonly class ClarityCommand
{
    /**
     * @param  array<int, mixed>  $arguments
     */
    private function __construct(private array $arguments) {}

    /**
     * Custom tag: `window.clarity("set", key, values)`.
     * Empty values are dropped and the list re-indexed.
     *
     * @param  string|array<int, mixed>  $values
     */
    public static function tag(string $key, string|array $values): self
    {
        $values = is_array($values) ? $values : [$values];
        $values = array_values(array_filter($values, static fn (mixed $value): bool => ! empty($value)));

        return new self(['set', $key, $values]);
    }

    /**
     * Identify: `window.clarity("identify", custom-id, session-id, page-id, friendly-name)`.
     * The user's `email` maps to custom-id and `name` to friendly-name.
     */
    public static function identify(?object $user, ?string $sessionId = null, ?string $pageId = null): self
    {
        return new self([
            'identify',
            data_get($user, 'email'),
            $sessionId,
            $pageId,
            data_get($user, 'name'),
        ]);
    }

    /**
     * Custom user ID: `window.clarity("identify", user-id, null, null, null)`.
     */
    public static function customUserId(string $userId): self
    {
        return new self(['identify', $userId, null, null, null]);
    }

    /**
     * Custom session ID: `window.clarity("identify", null, session-id, null, null)`.
     */
    public static function customSessionId(string $sessionId): self
    {
        return new self(['identify', null, $sessionId, null, null]);
    }

    /**
     * Custom page ID: `window.clarity("identify", null, null, page-id, null)`.
     */
    public static function customPageId(string $pageId): self
    {
        return new self(['identify', null, null, $pageId, null]);
    }

    /**
     * Consent. Version 'v2' (recommended) emits the `consentv2` object form;
     * 'v1' emits the deprecated boolean form. A single grant maps both storage
     * signals to the same state, per Microsoft's documented behavior.
     */
    public static function consent(bool $granted, string $version = 'v2'): self
    {
        if ($version === 'v1') {
            return new self(['consent', $granted]);
        }

        $state = $granted ? 'granted' : 'denied';

        return new self(['consentv2', [
            'ad_Storage' => $state,
            'analytics_Storage' => $state,
        ]]);
    }

    /**
     * The argument list to spread into `window.clarity(...)`.
     *
     * @return array<int, mixed>
     */
    public function arguments(): array
    {
        return $this->arguments;
    }
}
