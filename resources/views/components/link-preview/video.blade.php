@props(['title', 'author', 'mediaUrl'])

<div
    class="rounded-lg overflow-hidden"
    x-data="{
        player: null,
        initPlayer() {
            this.player = new PlyrManager(this.$refs.videoPlayerContainer, {
                mediaMetadata: {
                    title: @js($title),
                    artist: @js($author)
                },
                urls: {
                    download: @js($mediaUrl)
                }
            })
        }
    }"
    x-init="initPlayer()"
    wire:ignore
>
    <video
        class="object-cover"
        x-ref="videoPlayerContainer"
        player-src="{{ $mediaUrl }}"
        loop="loop"
        autoplay="autoplay"
        controls="controls"
        muted="muted"
        crossorigin=""
        title="{{ $title }}"
    ></video>
</div>
