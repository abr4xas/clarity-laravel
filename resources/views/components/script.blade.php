@props([
    'enabled' => true,
])

@if ($enabled && config('clarity.enabled'))
    <script type="text/javascript">
        (function(c,l,a,r,i,t,y){
            c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
            t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
            y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
        })(window, document, "clarity", "script", "{{ config('clarity.id') }}");
    </script>

    @php
        $commands = [];

        foreach (config('clarity.global_tags', []) as $key => $values) {
            $commands[] = \Abr4xas\ClarityLaravel\ClarityCommand::tag($key, $values)->arguments();
        }

        foreach (($clarity_auto_tags ?? []) as $key => $values) {
            $commands[] = \Abr4xas\ClarityLaravel\ClarityCommand::tag($key, $values)->arguments();
        }

        $autoIdentifyUser = $clarity_auto_identify_user ?? null;
        if ($autoIdentifyUser && config('clarity.auto_identify_users', false) && config('clarity.enabled_identify_api', false)) {
            $commands[] = \Abr4xas\ClarityLaravel\ClarityCommand::identify($autoIdentifyUser)->arguments();
        }
    @endphp

    @foreach ($commands as $arguments)
        @include('clarity::command', ['arguments' => $arguments])
    @endforeach
@endif
