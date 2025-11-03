<div wire:init="loadSection">
    @if ($this->moreByStudio->count())
        <section class="pb-8">
            <x-section-nav class="pt-4">
                <x-slot:title>
                    {{ __('More By :x', ['x' => $studio->name]) }}
                </x-slot:title>

                <x-slot:action>
                    @hasrole('superAdmin')
                        <x-button wire:click="$refresh">{{ __('Refresh') }}</x-button>
                    @endhasrole
                    <x-section-nav-link href="{{ route('studios.details', $studio) }}">{{ __('See All') }}</x-section-nav-link>
                </x-slot:action>
            </x-section-nav>

            <x-rows.small-lockup :animes="$this->moreByStudio" />
        </section>
    @elseif (!$readyToLoad)
        <section class="pb-8">
            <div class="flex gap-2 justify-between mb-5 pt-4 pl-4 pr-4">
                <div>
                    <p class="bg-secondary rounded-md" style="width: 168px; height: 28px"></p>
                    <p class="bg-secondary rounded-md" style="width: 228px; height: 22px"></p>
                </div>

                <div class="flex flex-wrap gap-2 justify-end"></div>
            </div>

            <div class="flex gap-4 justify-between pl-4 pr-4 snap-mandatory snap-x overflow-x-scroll no-scrollbar">
                <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow pb-2 shrink-0 snap-normal snap-center" style="height: 168px;"></div>
                <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow pb-2 shrink-0 snap-normal snap-center" style="height: 168px;"></div>
                <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow pb-2 shrink-0 snap-normal snap-center" style="height: 168px;"></div>
                <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow pb-2 shrink-0 snap-normal snap-center" style="height: 168px;"></div>
                <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow pb-2 shrink-0 snap-normal snap-center" style="height: 168px;"></div>
            </div>
        </section>
    @endif
</div>
