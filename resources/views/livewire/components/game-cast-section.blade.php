<div>
    @if ($castCount)
        <section class="pt-5 pb-8 pl-4 pr-4 border-t-2" wire:init="loadGameCast">
            <x-section-nav>
                <x-slot:title>
                    {{ __('Cast') }}
                </x-slot:title>

                <x-slot:action>
                    @hasrole('superAdmin')
                        <x-button wire:click="loadGameCast">{{ __('Refresh') }}</x-button>
                    @endhasrole
                    <x-section-nav-link href="{{ route('games.cast', $game) }}">{{ __('See All') }}</x-section-nav-link>
                </x-slot:action>
            </x-section-nav>

            <div class="flex justify-center">
                <x-spinner />
            </div>

            <div class="grid grid-flow-col-dense gap-4 justify-start overflow-x-scroll no-scrollbar">
                @foreach($gameCast as $cast)
                    <x-lockups.cast-lockup :cast="$cast" />
                @endforeach
            </div>
        </section>
    @endif
</div>
