@props([
    'enabled' => true,
    'user_id' => null,
])

@if ($enabled && config('clarity.enabled') && config('clarity.enabled_identify_api', false) && $user_id)
    <script type="text/javascript">
        (function(){
            function setCustomUserId() {
                if (typeof window.clarity === 'function') {
                    window.clarity("identify", "{{ $user_id }}", null, null, null);
                } else {
                    if (!window._clarityQueue) {
                        window._clarityQueue = [];
                    }
                    window._clarityQueue.push({
                        method: 'identify',
                        userId: "{{ $user_id }}",
                        sessionId: null,
                        pageId: null,
                        friendlyName: null
                    });
                    setTimeout(setCustomUserId, 100);
                }
            }
            setCustomUserId();
        })();
    </script>
@endif
