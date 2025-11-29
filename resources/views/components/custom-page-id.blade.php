@props([
    'enabled' => true,
    'page_id' => null,
])

@if ($enabled && config('clarity.enabled') && config('clarity.enabled_identify_api', false) && $page_id)
    <script type="text/javascript">
        (function(){
            function setCustomPageId() {
                if (typeof window.clarity === 'function') {
                    window.clarity("identify", null, null, "{{ $page_id }}", null);
                } else {
                    if (!window._clarityQueue) {
                        window._clarityQueue = [];
                    }
                    window._clarityQueue.push({
                        method: 'identify',
                        userId: null,
                        sessionId: null,
                        pageId: "{{ $page_id }}",
                        friendlyName: null
                    });
                    setTimeout(setCustomPageId, 100);
                }
            }
            setCustomPageId();
        })();
    </script>
@endif
