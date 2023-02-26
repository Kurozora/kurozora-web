<div>
    @if ($mediaSongsCount)
        <section class="pt-5 pb-8 pl-4 pr-4 border-t-2" wire:init="loadMediaSongs">
            <x-section-nav>
                <x-slot:title>
                    {{ __('Songs') }}
                </x-slot:title>

                <x-slot:action>
                    @hasrole('superAdmin')
                        <x-button wire:click="loadMediaSongs">{{ __('Refresh') }}</x-button>
                    @endhasrole
                    <x-section-nav-link href="{{ route('games.songs', $game) }}">{{ __('See All') }}</x-section-nav-link>
                </x-slot:action>
            </x-section-nav>

            <div class="flex justify-center">
                <x-spinner />
            </div>

            <x-rows.music-lockup :media-songs="$mediaSongs" />
        </section>
    @endif
</div>
