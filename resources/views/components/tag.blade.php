@props([
    'enabled' => true,
    'key' => null,
    'values' => [],
])

@if ($enabled && config('clarity.enabled') && $key)
    <script type="text/javascript">
        (function(){
            @php
                $valuesArray = is_array($values) ? $values : [$values];
                $valuesJson = json_encode(array_values(array_filter($valuesArray, fn($v) => !empty($v))));
            @endphp

            if (typeof window.clarity === 'function') {
                window.clarity("set", "{{ $key }}", {!! $valuesJson !!});
            }
        })();
    </script>
@endif
