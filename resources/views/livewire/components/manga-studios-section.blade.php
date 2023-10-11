<div wire:init="loadSection">
    @if ($this->studios->count())
        <section class="pt-5 pb-8 pl-4 pr-4 border-t-2">
            <x-section-nav>
                <x-slot:title>
                    {{ __('Studios') }}
                </x-slot:title>

                <x-slot:action>
                    @hasrole('superAdmin')
                        <x-button wire:click="$refresh">{{ __('Refresh') }}</x-button>
                    @endhasrole
                    <x-section-nav-link href="{{ route('manga.studios', $manga) }}">{{ __('See All') }}</x-section-nav-link>
                </x-slot:action>
            </x-section-nav>

            <div class="flex justify-center">
                <x-spinner />
            </div>

            <x-rows.studio-lockup :studios="$this->studios" />
        </section>
    @elseif (!$readyToLoad)
        <section class="pt-5 pb-8 pl-4 pr-4 border-t-2">
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
        </section>
    @endif
</div>
