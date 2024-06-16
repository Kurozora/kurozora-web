<div wire:init="loadSection">
    @if ($this->models->count())
        <section class="pt-5 pb-8 border-t-2">
            <x-section-nav>
                <x-slot:title>
                    {{ $this->title }}
                </x-slot:title>

                <x-slot:action>
                    @hasrole('superAdmin')
                        <x-button wire:click="$refresh">{{ __('Refresh') }}</x-button>
                    @endhasrole
                </x-slot:action>
            </x-section-nav>

            @switch($type)
                @case(\App\Models\Anime::class)
                    <x-rows.small-lockup :animes="$this->models" />
                    @break
                @case(\App\Models\Game::class)
                    <x-rows.small-lockup :games="$this->models" />
                    @break
            @endswitch
        </section>
    @elseif (!$readyToLoad)
        <section  class="pt-5 pb-8 border-t-2">
            <div>
                <div class="flex gap-2 justify-between mb-5">
                    <div>
                        <p class="bg-gray-200" style="width: 168px; height: 28px"></p>
                        <p class="bg-gray-200" style="width: 228px; height: 22px"></p>
                    </div>

                    <div class="flex flex-wrap gap-2 justify-end"></div>
                </div>

                <div class="flex gap-4 justify-between snap-mandatory snap-x overflow-x-scroll no-scrollbar">
                    <div class="bg-gray-200 w-64 md:w-80 flex-grow pb-2 shrink-0 snap-normal snap-center" style="height: 168px;"></div>
                    <div class="bg-gray-200 w-64 md:w-80 flex-grow pb-2 shrink-0 snap-normal snap-center" style="height: 168px;"></div>
                    <div class="bg-gray-200 w-64 md:w-80 flex-grow pb-2 shrink-0 snap-normal snap-center" style="height: 168px;"></div>
                    <div class="bg-gray-200 w-64 md:w-80 flex-grow pb-2 shrink-0 snap-normal snap-center" style="height: 168px;"></div>
                    <div class="bg-gray-200 w-64 md:w-80 flex-grow pb-2 shrink-0 snap-normal snap-center" style="height: 168px;"></div>
                </div>
            </div>
        </section>
    @endif
</div>
