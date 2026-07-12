@props([
    'enabled' => true,
    'user_id' => null,
])

@if ($enabled && config('clarity.enabled') && config('clarity.enabled_identify_api', false) && $user_id)
    @include('clarity::command', [
        'arguments' => \Abr4xas\ClarityLaravel\ClarityCommand::customUserId($user_id)->arguments(),
    ])
@endif
