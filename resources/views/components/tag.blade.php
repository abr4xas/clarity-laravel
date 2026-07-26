@props([
    'enabled' => true,
    'key' => null,
    'values' => [],
])

@if ($enabled && config('clarity.enabled') && $key)
    @include('clarity::command', [
        'arguments' => \Abr4xas\ClarityLaravel\ClarityCommand::tag($key, $values)->arguments(),
    ])
@endif
