@props([
    'enabled' => true,
    'session_id' => null,
])

@if ($enabled && config('clarity.enabled') && config('clarity.enabled_identify_api', false) && $session_id)
    <script type="text/javascript">
        (function(){
            function setCustomSessionId() {
                if (typeof window.clarity === 'function') {
                    window.clarity("identify", null, "{{ $session_id }}", null, null);
                } else {
                    if (!window._clarityQueue) {
                        window._clarityQueue = [];
                    }
                    window._clarityQueue.push({
                        method: 'identify',
                        userId: null,
                        sessionId: "{{ $session_id }}",
                        pageId: null,
                        friendlyName: null
                    });
                    setTimeout(setCustomSessionId, 100);
                }
            }
            setCustomSessionId();
        })();
    </script>
@endif
