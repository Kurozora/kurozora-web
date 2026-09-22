<div wire:init="loadSection">
    @if ($this->mediaStaff->count())
        <section class="pb-8">
            <x-section-nav class="pt-4 xl:safe-area-inset-scroll">
                <x-slot:title>
                    {{ __('Staff') }}
                </x-slot:title>

                <x-slot:action>
                    @hasrole('superAdmin')
                        <x-button wire:click="$refresh">{{ __('Refresh') }}</x-button>
                    @endhasrole
                    <x-section-nav-link href="{{ $this->seeAllUrl }}">{{ __('See All') }}</x-section-nav-link>
                </x-slot:action>
            </x-section-nav>

            <div class="grid grid-flow-col-dense gap-4 justify-start overflow-x-scroll no-scrollbar">
                <x-rows.person-lockup :media-staff="$this->mediaStaff" />
            </div>
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

            <x-skeletons.lockup-row lockup="person" />
        </section>
    @endif
</div>
