<div>
    @if ($studiosCount)
        <section class="pt-5 pb-8 pl-4 pr-4 border-t-2" wire:init="loadGameStudios">
            <x-section-nav>
                <x-slot:title>
                    {{ __('Studios') }}
                </x-slot:title>

                <x-slot:action>
                    @hasrole('superAdmin')
                        <x-button wire:click="loadGameStudios">{{ __('Refresh') }}</x-button>
                    @endhasrole
                    <x-section-nav-link href="{{ route('games.studios', $game) }}">{{ __('See All') }}</x-section-nav-link>
                </x-slot:action>
            </x-section-nav>

            <div class="flex justify-center">
                <x-spinner />
            </div>

            <x-rows.studio-lockup :studios="$studios" />
        </section>
    @endif
</div>
