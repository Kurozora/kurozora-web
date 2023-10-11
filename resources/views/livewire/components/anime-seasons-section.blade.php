<section class="pt-5 pb-8 pl-4 pr-4 border-t-2" wire:init="loadSection">
    @if ($this->seasons->count())
        <x-section-nav>
            <x-slot:title>
                {{ __('Seasons') }}
            </x-slot:title>

            <x-slot:action>
                @hasrole('superAdmin')
                    <x-button wire:click="$refresh">{{ __('Refresh') }}</x-button>
                @endhasrole
                <x-section-nav-link href="{{ route('anime.seasons', $anime) }}">{{ __('See All') }}</x-section-nav-link>
            </x-slot:action>
        </x-section-nav>

        <div class="grid grid-flow-col-dense gap-4 justify-start overflow-x-scroll no-scrollbar">
            @foreach($this->seasons as $season)
                <x-lockups.poster-lockup :season="$season" />
            @endforeach
        </div>
    @elseif (!$readyToLoad)
        <div class="flex gap-2 justify-between mb-5">
            <div>
                <p class="bg-gray-200" style="width: 168px; height: 28px"></p>
                <p class="bg-gray-200" style="width: 228px; height: 22px"></p>
            </div>

            <div class="flex flex-wrap gap-2 justify-end"></div>
        </div>

        <div class="flex gap-4 justify-between snap-x overflow-x-scroll no-scrollbar">
            <div class="bg-gray-200 w-64 md:w-80 flex-grow pb-2 shrink-0 snap-normal snap-center" style="height: 168px;"></div>
            <div class="bg-gray-200 w-64 md:w-80 flex-grow pb-2 shrink-0 snap-normal snap-center" style="height: 168px;"></div>
            <div class="bg-gray-200 w-64 md:w-80 flex-grow pb-2 shrink-0 snap-normal snap-center" style="height: 168px;"></div>
            <div class="bg-gray-200 w-64 md:w-80 flex-grow pb-2 shrink-0 snap-normal snap-center" style="height: 168px;"></div>
            <div class="bg-gray-200 w-64 md:w-80 flex-grow pb-2 shrink-0 snap-normal snap-center" style="height: 168px;"></div>
        </div>
    @endif
</section>
