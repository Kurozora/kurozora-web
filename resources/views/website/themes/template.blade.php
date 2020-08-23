<html lang="{{ app()->getLocale() }}" style="background-image: url({{ asset('img/static/star_bg_lg.jpg') }}); background-position: center center; background-attachment: fixed; background-size: cover;">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="text/css" href="{{ asset('img/static/favicon.ico') }}">

    <title>{{ isset($title) ? $title . ' | ' : null }}Kurozora Themes</title>

    {{-- Stylesheets --}}
    <link href="{{ asset('css/app.css') }}" rel="stylesheet"/>
</head>
<body>
    <div id="app">
        @include('website.resources.navigation.global')

        <main id="main">
            @yield('content')
        </main>

        @include('website.layouts.partials.footer')
    </div>
</body>
</html>
