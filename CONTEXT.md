# Domain glossary — clarity-laravel

The ubiquitous language for this package. Keep it small and sharp; add a term
only when the code names a concept that isn't obvious from its type.

## Command

A single Microsoft Clarity API call, normalized into the argument list for
`window.clarity(...)` — e.g. `['set', 'plan', ['premium']]` or
`['identify', 'a@b.com', null, null, 'Ana']`.

Owned by `ClarityCommand` (`src/ClarityCommand.php`), a pure value object: its
named constructors (`tag`, `identify`, `customUserId`, `customSessionId`,
`customPageId`, `consent`) do all the argument shaping — dropping empty tag
values, null-handling a user's `email`/`name`, choosing the `consentv2` object
form vs. the deprecated `consent` boolean form. It never reads config, so the
shaping is testable in isolation through its one seam, `arguments(): array`.

## Emitter

The single Blade partial (`resources/views/command.blade.php`) that turns a
Command's arguments into a `<script>`. It ensures Clarity's buffering stub
exists, then dispatches via `window.clarity.apply(null, …)`. Calls made before
the Clarity tag loads are buffered natively by Clarity (`clarity.q`) and
replayed on load — so there is exactly one queue, drained once by Clarity
itself. (This replaced a custom `_clarityQueue` that duplicated the native one.)

## Producer

Any public surface that creates a Command and hands it to the Emitter: the
Blade components (`<x-clarity::tag>`, `<x-clarity::identify>`, …) and the global
helper functions (`clarity_tag`, `clarity_identify`, …). Producers own the
`config('clarity.*')` guards (enabled, identify API, consent version); the
Command stays pure.
