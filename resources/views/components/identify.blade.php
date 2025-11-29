@props([
    'enabled' => true,
    'user' => null,
    'custom_session_id' => null,
    'custom_page_id' => null,
])

@if ($enabled && config('clarity.enabled') && config('clarity.enabled_identify_api'))
    <script type="text/javascript">
        (function(){
            function identifyUser() {
                if (typeof window.clarity === 'function') {
                    window.clarity(
                        "identify",
                        @if($user?->email) "{{$user->email}}" @else null @endif,
                        @if($custom_session_id) "{{$custom_session_id}}" @else null @endif,
                        @if($custom_page_id) "{{$custom_page_id}}" @else null @endif,
                        @if($user?->name) "{{$user->name}}" @else null @endif,
                    );
                } else {
                    if (!window._clarityQueue) {
                        window._clarityQueue = [];
                    }
                    window._clarityQueue.push({
                        method: 'identify',
                        userId: @if($user?->email) "{{$user->email}}" @else null @endif,
                        sessionId: @if($custom_session_id) "{{$custom_session_id}}" @else null @endif,
                        pageId: @if($custom_page_id) "{{$custom_page_id}}" @else null @endif,
                        friendlyName: @if($user?->name) "{{$user->name}}" @else null @endif,
                    });
                    setTimeout(identifyUser, 100);
                }
            }
            identifyUser();
        })();
    </script>
@endif
