@props([
    'enabled' => true,
    'granted' => true,
])

@if ($enabled && config('clarity.enabled'))
    <script type="text/javascript">
        (function(){
            @php
                $consentVersion = config('clarity.consent_version', 'v2');
            @endphp
            function setConsent() {
                if (typeof window.clarity === 'function') {
                    @if($consentVersion === 'v1')
                        window.clarity("consent", {{ $granted ? 'true' : 'false' }});
                    @else
                        window.clarity("consent", "{{ $granted ? 'granted' : 'denied' }}");
                    @endif
                } else {
                    setTimeout(setConsent, 100);
                }
            }
            setConsent();
        })();
    </script>
@endif
