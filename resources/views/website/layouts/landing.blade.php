<!doctype html>
<html lang="{{ app()->getLocale() }}" style="background-image: url({{ asset('img/static/star_bg_lg.jpg') }});">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="text/css" href="{{ asset('img/static/logo_sm.png') }}">

    @if(isset($page['title']))
        <title>{{ $page['title'] }}</title>
        <meta property="og:title" content="{{ $page['title'] }}" />
    @else
        <title>Kurozora App</title>
    @endif

    @if(isset($page['image']) && $page['image'] !== null)
        <meta property="og:image" content="{{ $page['image'] }}" />
    @endif

    @if(isset($page['type']) && $page['type'] !== null)
        <meta property="og:type" content="{{ $page['type'] }}" />
    @endif

    @if(isset($page['no_index']) && $page['no_index'])
        <meta name="robots" content="noindex,nofollow" />
    @endif

    {{-- jQuery --}}
    <script type="text/javascript" src="{{ asset('js/jquery-3.3.1.min.js') }}"></script>

    {{-- Landing stylesheet --}}
    <link href="{{ asset('css/kurozora-landing.css') }}" rel="stylesheet">
</head>
<body>
    <div class="middle-div">
        @yield('content')
    </div>

    <script type="text/javascript">
        $(document).ready(function() {
            $('.middle-div').fadeIn(1000);
        });
    </script>
</body>
</html>
