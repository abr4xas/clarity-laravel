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

            function setTag() {
                if (typeof window.clarity === 'function') {
                    window.clarity("set", "{{ $key }}", {!! $valuesJson !!});
                } else {
                    // Queue the tag if Clarity isn't loaded yet
                    if (!window._clarityQueue) {
                        window._clarityQueue = [];
                    }
                    window._clarityQueue.push({
                        method: "set",
                        key: "{{ $key }}",
                        values: {!! $valuesJson !!}
                    });
                    // Try again after a short delay
                    setTimeout(setTag, 100);
                }
            }
            setTag();
        })();
    </script>
@endif
