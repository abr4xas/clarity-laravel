@props([
    'enabled' => true,
    'session_id' => null,
])

@if ($enabled && config('clarity.enabled') && config('clarity.enabled_identify_api', false) && $session_id)
    @include('clarity::command', [
        'arguments' => \Abr4xas\ClarityLaravel\ClarityCommand::customSessionId($session_id)->arguments(),
    ])
@endif
