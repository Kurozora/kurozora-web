<div wire:init="loadSection">
    @if ($this->studios->count())
        <section class="pb-8">
            <x-section-nav class="pt-4 xl:safe-area-inset-scroll">
                <x-slot:title>
                    {{ __('Studios') }}
                </x-slot:title>

                <x-slot:action>
                    <x-spinner />

                    @hasrole('superAdmin')
                        <x-button wire:click="$refresh">{{ __('Refresh') }}</x-button>
                    @endhasrole

                    <x-section-nav-link href="{{ $this->seeAllUrl }}">{{ __('See All') }}</x-section-nav-link>
                </x-slot:action>
            </x-section-nav>

            <x-rows.studio-lockup :studios="$this->studios" />
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

            <x-skeletons.lockup-row lockup="studio" />
        </section>
    @endif
</div>
