@props([
    'enabled' => true,
])

@if ($enabled && config('clarity.enabled'))
    <script type="text/javascript">
        (function(){
            // Initialize queue if it doesn't exist
            if (!window._clarityQueue) {
                window._clarityQueue = [];
            }

            // Process queued items when Clarity is ready
            function processQueue() {
                if (typeof window.clarity === 'function' && window._clarityQueue && window._clarityQueue.length > 0) {
                    while (window._clarityQueue.length > 0) {
                        var item = window._clarityQueue.shift();
                        if (item.method === 'set') {
                            window.clarity("set", item.key, item.values);
                        } else if (item.method === 'identify') {
                            window.clarity("identify", item.userId, item.sessionId, item.pageId, item.friendlyName);
                        }
                    }
                } else if (window._clarityQueue && window._clarityQueue.length > 0) {
                    setTimeout(processQueue, 100);
                }
            }

            // Start processing queue
            processQueue();
        })();
    </script>
@endif
