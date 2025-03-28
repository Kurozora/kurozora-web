<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta property="og:url" content="{{ url()->current() }}" />
        <meta property="og:site_name" content="{{ config('app.name') }}" />
        <meta property="twitter:site" content="{{ '@' . config('social.twitter.username') }}" />
        <meta name="theme-color" content="{{ $themeColor ?? '#F3F4F6' }}">
        <meta name="theme-color" content="{{ $lightThemeColor ?? '#F3F4F6' }}" media="(prefers-color-scheme: light)">
        <meta name="theme-color" content="{{ $darkThemeColor ?? '#353A50' }}" media="(prefers-color-scheme: dark)">
        {{ $meta ?? '' }}

        @if (empty($title))
            <title>{{ config('app.name') }}</title>
        @else
            <title>{{ $title . ' — ' . config('app.name') }}</title>
        @endif

        @if (empty($description))
            <meta name="description" content="{{ __('app.description') }}" />
        @else
            <meta name="description" content="{{ $description }}" />
        @endif

        <!-- Favicon -->
        <link rel="shortcut icon" href="{{ url(asset('favicon.ico')) }}" />

        <!-- CDN -->
        <link rel="preconnect" href="{{ config('filesystems.disks.s3.url') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:100,200,300,400,500,600,700,800,900&display=swap" rel="stylesheet" />

        <!-- Styles -->
        <link rel="preload" href="{{ url(mix('css/app.css')) }}" as="style">
        <link rel="stylesheet" href="{{ url(mix('css/app.css')) }}" />
        @livewireStyles
        {{ $styles ?? '' }}

        {{-- Search --}}
        <link rel="search" type="application/opensearchdescription+xml" title="{{ config('app.name') }}" href="{{ asset('opensearch.xml') }}">

        <!-- Scripts -->
        <script src="{{ url(mix('js/manifest.js')) }}" defer data-navigate-track></script>
        <script src="{{ url(mix('js/vendor.js')) }}" defer data-navigate-track></script>
        <script src="{{ url(mix('js/settings.js')) }}" defer data-navigate-track></script>
        <script src="{{ url(mix('js/app.js')) }}" defer data-navigate-track></script>
        @if (app()->isLocal())
            <script src="{{ url(mix('js/debug.js')) }}" defer data-navigate-track></script>
        @endif

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}" />
    </head>

    <body class="app-theme bg-primary text-primary">
        {{ $slot }}

        @livewireScripts
        {{ $scripts ?? '' }}
        <script src="https://js-cdn.music.apple.com/musickit/v1/musickit.js"></script>
    </body>
</html>
