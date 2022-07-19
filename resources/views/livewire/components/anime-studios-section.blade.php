<div>
    @if ($studiosCount)
        <section class="pt-5 pb-8 px-4 border-t-2" wire:init="loadAnimeStudios">
            <x-section-nav>
                <x-slot:title>
                    {{ __('Studios') }}
                </x-slot>

                <x-slot:action>
                    @hasrole('superAdmin')
                        <x-button wire:click="loadAnimeStudios">{{ __('Refresh') }}</x-button>
                    @endhasrole
                    <x-section-nav-link href="{{ route('anime.studios', $anime) }}">{{ __('See All') }}</x-section-nav-link>
                </x-slot>
            </x-section-nav>

            <div class="flex justify-center">
                <x-spinner />
            </div>

            <x-rows.studio-lockup :studios="$studios" />
        </section>
    @endif
</div>
