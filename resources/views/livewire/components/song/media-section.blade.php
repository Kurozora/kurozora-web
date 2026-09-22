<div wire:init="loadSection">
    @if ($this->models->count())
        <section class="pb-8">
            <x-section-nav class="pt-4 xl:safe-area-inset-scroll">
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
        <section  class="pb-8">
            <div class="flex gap-2 justify-between mb-5 pt-4 pl-4 pr-4 xl:safe-area-inset-scroll">
                <div>
                    <p class="bg-secondary rounded-md" style="width: 168px; height: 28px"></p>
                    <p class="bg-secondary rounded-md" style="width: 228px; height: 22px"></p>
                </div>

                <div class="flex flex-wrap gap-2 justify-end"></div>
            </div>

            <x-skeletons.lockup-row />
        </section>
    @endif
</div>
