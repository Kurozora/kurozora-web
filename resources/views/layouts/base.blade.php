<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta property="og:url" content="{{ url()->current() }}" />
        <meta property="og:site_name" content="{{ config('app.name') }}" />
        <meta property="twitter:site" content="{{ '@' . config('social.twitter.username') }}" />
        <meta name="theme-color" content="{{ $themeColor ?? '#F3F4F6'}}">
        <meta name="theme-color" content="{{ $lightThemeColor ?? '#F3F4F6'}}" media="(prefers-color-scheme: light)">
        <meta name="theme-color" content="{{ $darkThemeColor ?? '#353A50'}}" media="(prefers-color-scheme: dark)">
        {{ $meta ?? '' }}

        @desktop
        @else
            @if (!empty($appArgument))
                <meta name="apple-itunes-app" content="app-id={{ config('app.ios.id') }}, app-argument={{ config('app.ios.protocol') }}{{ $appArgument }}" />
            @endif
        @enddesktop

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

        <!-- Fonts -->
        <link rel="preload" href="https://rsms.me/inter/inter.css" as="style" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />

        <!-- Styles -->
        <link rel="preload" href="{{ url(mix('css/app.css')) }}" as="style">
        <link rel="stylesheet" href="{{ url(mix('css/app.css')) }}" />
        @livewireStyles
        {{ $styles ?? '' }}

        <!-- Scripts -->
        <script src="{{ url(mix('js/app.js')) }}" defer></script>
        <script src="https://js-cdn.music.apple.com/musickit/v1/musickit.js" defer></script>
        @if (app()->environment('local'))
            <script src="{{ url(mix('js/debug.js')) }}" defer></script>
        @endif

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}" />
    </head>

    <body
        class="bg-white dark:bg-black"
        x-data="{
            music: null,
            musicIsPlaying: false,
            currentMusicID: '',
            async playSong(song) {
                if (!!song) {
                    if (this.currentMusicID === song.id && MusicKit.getInstance().player.isPlaying) {
                        MusicKit.getInstance().player.pause()
                        this.musicIsPlaying = false;
                    } else if (this.currentMusicID === song.id) {
                        MusicKit.getInstance().player.play()
                        this.musicIsPlaying = true;
                    } else {
                        await this.setQueueItems([song]).then(function () {
                            MusicKit.getInstance().player.play()
                        })
                        this.musicIsPlaying = true;
                    }

                    this.currentMusicID = song.id;
                }
            },
            async setQueueItems(items) {
                const filteredItems = items.filter(item => item);
                if (filteredItems.length === 0) { return; }

                await MusicKit.getInstance().setQueue({
                    items: filteredItems.map(item => this.createMediaItem(item))
                });
            },
            createMediaItem(song) {
                return {
                    ...song,
                    container: {
                      id: song.id
                    }
                };
            },
            initMusicKit() {
                MusicKit.configure({
                    // MusicKit global is now defined
                    developerToken: '{{ config('services.apple.client_secret') }}',
                    app: {
                        build: '{{ config('app.version') }}',
                        icon: '{{ asset('images/static/icon/app_icon.webp') }}',
                        name: '{{ config('app.name') }}',
                        version: '{{ config('app.version') }}'
                    }
                });
                this.music = MusicKit.getInstance();
            }
        }"
        x-on:musickitloaded.window="initMusicKit()"
    >
        @livewire('navigation-dropdown')

        @if(Session::has('success'))
            <x-alert :message="Session::get('success')"></x-alert>
        @endif

        {{ $slot }}

        <x-footer />

        @livewireScripts
        {{ $scripts ?? '' }}
    </body>
</html>
