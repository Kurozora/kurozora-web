<div>
    @if ($castCount)
        <section class="pt-5 pb-8 pl-4 pr-4 border-t-2" wire:init="loadMangaCast">
            <x-section-nav>
                <x-slot:title>
                    {{ __('Cast') }}
                </x-slot:title>

                <x-slot:action>
                    @hasrole('superAdmin')
                    <x-button wire:click="loadMangaCast">{{ __('Refresh') }}</x-button>
                    @endhasrole
                    <x-section-nav-link href="{{ route('manga.cast', $manga) }}">{{ __('See All') }}</x-section-nav-link>
                </x-slot:action>
            </x-section-nav>

            <div class="flex justify-center">
                <x-spinner />
            </div>

            <div class="grid grid-flow-col-dense gap-4 justify-start overflow-x-scroll no-scrollbar">
                @foreach($mangaCast as $cast)
                    <x-lockups.character-lockup :character="$cast->character" :cast-role="$cast->castRole->name" />
                @endforeach
            </div>
        </section>
    @endif
</div>