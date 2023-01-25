<div>
    @if ($studiosCount)
        <section class="pt-5 pb-8 pl-4 pr-4 border-t-2" wire:init="loadMangaStudios">
            <x-section-nav>
                <x-slot:title>
                    {{ __('Studios') }}
                </x-slot:title>

                <x-slot:action>
                    @hasrole('superAdmin')
                        <x-button wire:click="loadMangaStudios">{{ __('Refresh') }}</x-button>
                    @endhasrole
                    <x-section-nav-link href="{{ route('manga.studios', $manga) }}">{{ __('See All') }}</x-section-nav-link>
                </x-slot:action>
            </x-section-nav>

            <div class="flex justify-center">
                <x-spinner />
            </div>

            <x-rows.studio-lockup :studios="$studios" />
        </section>
    @endif
</div>
