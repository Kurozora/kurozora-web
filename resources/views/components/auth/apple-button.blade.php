@props(['hasBorder' => false, 'backgroundColor' => 'black', 'borderRadius' => 15])

<a href="{{ route('siwa.sign-in') }}">
    <div id="appleid-signin" class="w-52 h-8" data-color="{{ $backgroundColor }}" data-border="{{ $hasBorder }}" data-border-radius="{{ $borderRadius }}"></div>
    <script type="text/javascript" src="https://appleid.cdn-apple.com/appleauth/static/jsapi/appleid/1/en_US/appleid.auth.js"></script>
</a>
