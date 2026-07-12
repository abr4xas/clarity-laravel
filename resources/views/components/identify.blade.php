@props([
    'enabled' => true,
    'user' => null,
    'custom_session_id' => null,
    'custom_page_id' => null,
])

@if ($enabled && config('clarity.enabled') && config('clarity.enabled_identify_api'))
    @include('clarity::command', [
        'arguments' => \Abr4xas\ClarityLaravel\ClarityCommand::identify($user, $custom_session_id, $custom_page_id)->arguments(),
    ])
@endif
