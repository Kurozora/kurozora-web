<div>
    @if ($animeSongsCount)
        <section class="pt-5 pb-8 pl-4 pr-4 border-t-2" wire:init="loadAnimeSongs">
            <x-section-nav>
                <x-slot:title>
                    {{ __('Songs') }}
                </x-slot:title>

                <x-slot:action>
                    @hasrole('superAdmin')
                        <x-button wire:click="loadAnimeSongs">{{ __('Refresh') }}</x-button>
                    @endhasrole
                    <x-section-nav-link href="{{ route('anime.songs', $anime) }}">{{ __('See All') }}</x-section-nav-link>
                </x-slot:action>
            </x-section-nav>

            <div class="flex justify-center">
                <x-spinner />
            </div>

            <x-rows.music-lockup :anime-songs="$animeSongs" />
        </section>
    @endif
</div>
