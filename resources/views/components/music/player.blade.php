<div
    data-music-player
    data-music-song
    class="fixed inset-x-0 bottom-0 z-40 px-4 pb-4 pointer-events-none translate-y-full opacity-0 transition-[transform,opacity] duration-300 ease-out"
>
    <div class="pointer-events-auto mx-auto flex max-w-md items-center gap-3 rounded-2xl p-2 backdrop-blur bg-blur">
        <button type="button" data-music-player-lyrics class="flex min-w-0 flex-1 items-center gap-3 text-left">
            <span class="relative block h-11 w-11 shrink-0 overflow-hidden rounded-lg">
                <img
                    data-music-player-artwork
                    class="h-full w-full object-cover bg-secondary"
                    width="96" height="96" alt=""
                    src="{{ asset('images/static/placeholders/music_album.webp') }}"
                >

                <span class="absolute inset-0 rounded-lg border border-solid border-black/20"></span>
            </span>

            <span class="flex min-w-0 flex-1 flex-col gap-0.5">
                <span data-music-player-title class="text-xs font-bold leading-tight"></span>
                <span data-music-player-artist class="text-[11px] leading-tight text-secondary"></span>
            </span>
        </button>

        <button
            type="button"
            data-music-play
            class="flex h-11 w-11 shrink-0 items-center justify-center"
            title="{{ __('Play') }}"
        >
            <span data-music-icon="play" class="inline-flex">@svg('play_fill', 'fill-current', ['width' => 20])</span>
            <span data-music-icon="pause" class="hidden inline-flex">@svg('pause_fill', 'fill-current', ['width' => 20])</span>
        </button>

        <x-dropdown id="music-player-menu" align="top" width="64" :overflow="false" contentClasses="bg-secondary p-1" :auto-placement="true">
            <x-slot:trigger>
                <button
                    type="button"
                    class="flex h-11 w-11 shrink-0 items-center justify-center"
                    title="{{ __('More Settings') }}"
                >
                    @svg('ellipsis', 'fill-current', ['width' => 20])
                </button>
            </x-slot:trigger>

            <x-slot:content>
                <x-menu.music :goto="true" />
            </x-slot:content>
        </x-dropdown>
    </div>
</div>
