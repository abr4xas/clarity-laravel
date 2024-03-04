@props([
    'enabled' => null
])

<div>
    <p>test</p>
    <x-clarity::script :enabled="$enabled" />
</div>
