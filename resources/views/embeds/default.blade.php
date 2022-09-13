<div
    style="all: inherit;"
    x-data="{
        player: null,
        initPlayer() {
            this.player = new PlyrManager('#videoPlayerContainer', {
                mediaMetadata: {
                    title: @js($title),
                    artist: @js($artist),
                    album: @js($album),
                    artwork: @js($artworks)
                },
                poster: @js($poster),
                urls: {
                    download: @js($url)
                },
                youtube: {
                    origin: '{{ config('app.url') }}'
                },
                currentTime: {{ $currentTime ?? 0 }}
            })
        }
    }"
    x-init="initPlayer()"
    wire:ignore
>
    <video
        id="videoPlayerContainer"
        class="m-auto aspect-video"
        player-src="{{ $url }}"
        width="1920"
        height="1080"
        autoplay="autoplay"
        controls="controls"
        crossorigin=""
        poster="{{ $poster }}"
    >
    </video>
</div>
