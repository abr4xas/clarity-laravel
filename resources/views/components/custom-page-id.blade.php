@props([
    'enabled' => true,
    'page_id' => null,
])

@if ($enabled && config('clarity.enabled') && config('clarity.enabled_identify_api', false) && $page_id)
    @include('clarity::command', [
        'arguments' => \Abr4xas\ClarityLaravel\ClarityCommand::customPageId($page_id)->arguments(),
    ])
@endif
