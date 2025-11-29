@props([
    'enabled' => true
])

@if ($enabled && config('clarity.enabled'))
    <script type="text/javascript">
        (function(c,l,a,r,i,t,y){
            // Initialize queue if it doesn't exist
            if (!window._clarityQueue) {
                window._clarityQueue = [];
            }
            c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
            t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
            y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
        })(window, document, "clarity", "script", "{{ config('clarity.id') }}");
    </script>
    @php
        $globalTags = config('clarity.global_tags', []);
        $autoTags = $clarity_auto_tags ?? [];
        $autoIdentifyUser = $clarity_auto_identify_user ?? null;
    @endphp
    @if (!empty($globalTags) || !empty($autoTags) || $autoIdentifyUser)
        <script type="text/javascript">
            (function(){
                function initializeClarity() {
                    if (typeof window.clarity === 'function') {
                        // Process global tags
                        @if(!empty($globalTags))
                            @foreach($globalTags as $key => $values)
                                @php
                                    $valuesArray = is_array($values) ? $values : [$values];
                                    $valuesJson = json_encode(array_values(array_filter($valuesArray, fn($v) => !empty($v))));
                                @endphp
                                window.clarity("set", "{{ $key }}", {!! $valuesJson !!});
                            @endforeach
                        @endif

                        // Process auto-tags from middleware
                        @if(!empty($autoTags))
                            @foreach($autoTags as $key => $values)
                                @php
                                    $valuesArray = is_array($values) ? $values : [$values];
                                    $valuesJson = json_encode(array_values(array_filter($valuesArray, fn($v) => !empty($v))));
                                @endphp
                                window.clarity("set", "{{ $key }}", {!! $valuesJson !!});
                            @endforeach
                        @endif

                        // Process auto-identify
                        @if($autoIdentifyUser && config('clarity.auto_identify_users', false) && config('clarity.enabled_identify_api', false))
                            window.clarity(
                                "identify",
                                @if($autoIdentifyUser->email) "{{ $autoIdentifyUser->email }}" @else null @endif,
                                null,
                                null,
                                @if($autoIdentifyUser->name) "{{ $autoIdentifyUser->name }}" @else null @endif
                            );
                        @endif

                        // Process queued items
                        if (window._clarityQueue && window._clarityQueue.length > 0) {
                            while (window._clarityQueue.length > 0) {
                                var item = window._clarityQueue.shift();
                                if (item.method === 'set') {
                                    window.clarity("set", item.key, item.values);
                                } else if (item.method === 'identify') {
                                    window.clarity("identify", item.userId, item.sessionId, item.pageId, item.friendlyName);
                                }
                            }
                        }
                    } else {
                        setTimeout(initializeClarity, 100);
                    }
                }
                initializeClarity();
            })();
        </script>
    @endif
@endif
