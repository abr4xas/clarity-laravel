@props([
    'enabled' => true,
    'granted' => true,
])

@if ($enabled && config('clarity.enabled'))
    @include('clarity::command', [
        'arguments' => \Abr4xas\ClarityLaravel\ClarityCommand::consent($granted, config('clarity.consent_version', 'v2'))->arguments(),
    ])
@endif
