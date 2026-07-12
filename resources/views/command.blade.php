{{-- Emits a single Clarity command. Ensures the buffering stub exists so calls
     made before the tag loads are queued natively by Clarity (clarity.q), then
     dispatches. $arguments is the list produced by ClarityCommand::arguments(). --}}
<script type="text/javascript">
    window.clarity = window.clarity || function () { (window.clarity.q = window.clarity.q || []).push(arguments); };
    window.clarity.apply(null, {{ \Illuminate\Support\Js::from($arguments) }});
</script>
