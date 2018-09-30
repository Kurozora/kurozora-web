<html>
    <head>
        <title>{{ $title or 'Home' }} | Kurozora</title>

        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="icon" type="text/css" href="{{ asset('img/static/logo_sm.png') }}">

        {{-- Global CSS --}}
        <link type="text/css" rel="stylesheet" href="{{ asset('css/global.css') }}">

        {{-- Google Material icons --}}
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

        {{-- Materialize CSS --}}
        <link type="text/css" rel="stylesheet" href="{{ asset('css/materialize.min.css') }}" />

        {{-- Load custom CSS per page --}}
        @if(isset($stylesheets) && is_array($stylesheets))
            @foreach ($stylesheets as $stylesheet)
                <link type="text/css" rel="stylesheet" href="{{ $stylesheet }}">
            @endforeach
        @endif

        {{-- Load jQuery --}}
        <script type="text/javascript" src="{{ asset('js/jquery-3.3.1.min.js') }}"></script>

        {{-- Load Slick carousel --}}
        <link rel="stylesheet" type="text/css" href="{{ asset('css/slick.css') }}"/>
        <link rel="stylesheet" type="text/css" href="{{ asset('css/slick-theme.css') }}"/>

        {{-- Load custom JS per page --}}
        @if(isset($scripts) && is_array($scripts))
            @foreach ($scripts as $script)
                <script type="text/javascript" src="{{ $script }}"></script>
            @endforeach
        @endif

        {{-- Load Kurozora JS --}}
        <script type="text/javascript" src="{{ asset('js/kurozora.js') }}"></script>
    </head>

    <body id="templated-body" class="kurozora-purple">
        {{-- Navigation bar --}}
        <nav class="kurozora-purple">
            <div class="nav-wrapper">
                <a href="{{ url('/') }}" class="brand-logo">
                    <img src="{{ asset('img/static/logo_sm.png') }}" alt="Kurozora App logo" />
                </a>
                <a href="#" data-target="mobile-nav" class="sidenav-trigger"><i class="material-icons">menu</i></a>
                <ul class="right hide-on-med-and-down">
                    <li>
                        <a href="{{ url('/login') }}">
                            <i class="material-icons left">lock_outline</i>Login
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        {{-- Mobile side nav --}}
        <ul class="sidenav" id="mobile-nav">
            <li>
                <a href="{{ url('/login') }}">
                    <i class="material-icons left">lock_outline</i>Login
                </a>
            </li>
        </ul>

        @yield('content')

        {{-- Footer --}}
        <footer id="main-footer">
            <div class="container">
                <div class="row footer-row">
                    <div class="col s12 m2">
                        <span class="copy-text">&copy; {{ date('Y') }} Kurozora</span>
                    </div>

                    <div class="col s12 m10">
                        <ul class="footer-links">
                            <li>
                                <a href="{{ url('/privacy') }}">Privacy</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </footer>

        {{-- Load Materialize JS --}}
        <script type="text/javascript" src="{{ asset('js/materialize.min.js') }}"></script>

        {{-- Load Slick carousel JS --}}
        <script type="text/javascript" src="{{ asset('js/slick.min.js') }}"></script>
    </body>
</html>