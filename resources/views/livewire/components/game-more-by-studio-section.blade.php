<div wire:init="loadMoreByStudio">
    @if ($moreByStudioCount)
        <section id="moreByStudio" class="pt-5 pb-8 pl-4 pr-4 border-t-2">
            <x-section-nav>
                <x-slot:title>
                    {{ __('More By :x', ['x' => $studio->name]) }}
                </x-slot:title>

                <x-slot:action>
                    @hasrole('superAdmin')
                        <x-button wire:click="loadMoreByStudio">{{ __('Refresh') }}</x-button>
                    @endhasrole
                    <x-section-nav-link href="{{ route('studios.details', $studio) }}">{{ __('See All') }}</x-section-nav-link>
                </x-slot:action>
            </x-section-nav>

            <div class="flex justify-center">
                <x-spinner />
            </div>

            <x-rows.small-lockup :games="$moreByStudio" />
        </section>
    @endif
</div>
