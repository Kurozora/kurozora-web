<div>
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
    @endif
</div>
