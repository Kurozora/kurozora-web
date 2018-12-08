<html>
<head>
    <title>{{ $title or 'Home' }} | Kurozora Admin</title>

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="icon" type="text/css" href="{{ asset('img/static/logo_sm.png') }}">

    {{-- Google Material icons --}}
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    {{-- Materialize CSS --}}
    <link type="text/css" rel="stylesheet" href="{{ asset('css/materialize.min.css') }}" />

    {{-- Admin panel global --}}
    <link type="text/css" rel="stylesheet" href="{{ asset('css/admin_panel_global.css') }}" />

    {{-- Load jQuery --}}
    <script type="text/javascript" src="{{ asset('js/jquery-3.3.1.min.js') }}"></script>

    {{-- Load custom JS per page --}}
    @if(isset($scripts) && is_array($scripts))
        @foreach ($scripts as $script)
            <script type="text/javascript" src="{{ $script }}"></script>
        @endforeach
    @endif
</head>

<body class="kurozora-purple">
{{-- Side nav --}}
@if(!isset($hide_sidebar) || !$hide_sidebar)
<ul id="slide-out" class="sidenav sidenav-fixed">
    <li>
        <a href="{{ url('/admin') }}">
            <i class="material-icons">insert_chart</i> Dashboard
        </a>
    </li>
    <li>
        <a href="{{ url('/admin/users') }}">
            <i class="material-icons">accessibility</i> Users
        </a>
    </li>
    <li>
        <a href="{{ url('/admin/logout') }}">
            <i class="material-icons">exit_to_app</i> Sign out
        </a>
    </li>
</ul>
<a href="#" data-target="slide-out" class="sidenav-trigger"><i class="material-icons">menu</i></a>
@endif

<div class="@if(!isset($hide_sidebar) || !$hide_sidebar) wrapper @endif">
    @yield('content')
    <div class="push"></div>
</div>

{{-- Load Materialize JS --}}
<script type="text/javascript" src="{{ asset('js/materialize.min.js') }}"></script>
</body>
</html>