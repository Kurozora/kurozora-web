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

    {{-- Stylesheets --}}
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>

