<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        {{ $meta ?? '' }}

        @if (empty($title))
            <title>{{ config('app.name') }}</title>
        @else
            <title>{{ $title . ' - ' . config('app.name') }}</title>
        @endif

        <!-- Favicon -->
        <link rel="shortcut icon" href="{{ url(asset('favicon.ico')) }}">

        <!-- CDN -->
        <link rel="preconnect" href="{{ config('filesystems.disks.s3.url') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:100,200,300,400,500,600,700,800,900&display=swap" rel="stylesheet" />

        <!-- Styles -->
        <link rel="stylesheet" href="{{ url(mix('css/app.css')) }}">
        @livewireStyles

        {{-- Search --}}
        <link rel="search" type="application/opensearchdescription+xml" title="{{ config('app.name') }}" href="{{ asset('opensearch.xml') }}">

        <!-- Scripts -->
        <script src="{{ url(mix('js/settings.js')) }}" defer data-navigate-track></script>
        <script src="{{ url(mix('js/app.js')) }}" defer data-navigate-track></script>

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">
    </head>

    <body class="app-theme bg-primary text-primary">
        {{ $slot }}

        @livewireScripts
    </body>
</html>
