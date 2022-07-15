<div>
    @if ($animeSongsCount)
        <section class="pt-5 pb-8 px-4 border-t-2" wire:init="loadAnimeSongs">
            <x-section-nav>
                <x-slot:title>
                    {{ __('Songs') }}
                </x-slot>

                <x-slot:action>
                    @hasrole('superAdmin')
                        <x-button wire:click="loadAnimeSongs">{{ __('Refresh') }}</x-button>
                    @endhasrole
                    <x-section-nav-link href="{{ route('anime.songs', $anime) }}">{{ __('See All') }}</x-section-nav-link>
                </x-slot>
            </x-section-nav>

            <div class="flex justify-center">
                <x-spinner />
            </div>

            <div class="grid grid-flow-col-dense gap-4 justify-start overflow-x-scroll no-scrollbar">
                @foreach($animeSongs as $animeSong)
                    <x-lockups.music-lockup :anime-song="$animeSong" />
                @endforeach
            </div>
        </section>
    @endif
</div>
