<div wire:init="loadSection">
    @if ($this->moreByStudio->count())
        <section class="pb-8">
            <x-section-nav class="pt-4 xl:safe-area-inset-scroll">
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

            @switch ($kind)
                @case (\App\Enums\UserLibraryKind::Anime)
                    <x-rows.small-lockup :animes="$this->moreByStudio" />
                    @break

                @case (\App\Enums\UserLibraryKind::Manga)
                    <x-rows.small-lockup :mangas="$this->moreByStudio" />
                    @break

                @default
                    <x-rows.small-lockup :games="$this->moreByStudio" />
            @endswitch
        </section>
    @elseif (!$readyToLoad)
        <section class="pb-8">
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
