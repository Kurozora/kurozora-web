<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="text/css" href="{{ asset('img/static/logo_sm.png') }}">

    <title>{{ isset($title) ? $title . ' | ' : null }}Kurozora Themes</title>

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous">

    <link rel="stylesheet" href="{{ asset('css/app.css') }}" />
</head>
<body style="background-image: url({{ asset('img/static/star_bg_lg.jpg') }}); background-position: center center; background-attachment: fixed; background-size: cover;">
    <nav class="navbar" role="navigation" aria-label="main navigation">
        <div class="navbar-brand">
            <a role="button" class="navbar-burger burger" aria-label="menu" aria-expanded="false" data-target="themesNavbar">
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
            </a>
        </div>

        <div id="themesNavbar" class="navbar-menu">
            <div class="navbar-start">
                <a class="navbar-item" href="{{ route('home') }}">
                    <span class="icon">
                        <i class="fas fa-chevron-left"></i>
                    </span>
                    &nbsp;Back to Kurozora
                </a>
            </div>

            <div class="navbar-end">
                <div class="navbar-item">
                    <div class="buttons">
                        <a class="button is-primary"
                           href="{{ (\Illuminate\Support\Facades\Route::currentRouteName() !== 'themes.create') ? route('themes.create') : '#' }}"
                            {{ (\Illuminate\Support\Facades\Route::currentRouteName() == 'themes.create') ? 'disabled' : '' }}
                        >
                            <strong>Create your own</strong>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div id="app">
        @yield('content')
    </div>

    {{-- iro color picker --}}
    <script src="https://cdn.jsdelivr.net/npm/@jaames/iro/dist/iro.min.js"></script>

    {{-- jQuery --}}
    <script type="text/javascript" src="{{ asset('js/jquery-3.3.1.min.js') }}"></script>

    {{-- App Script --}}
    <script type="text/javascript" src="{{ asset('js/app.js') }}"></script>
</body>
</html>
