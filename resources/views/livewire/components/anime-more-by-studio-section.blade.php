<div wire:init="loadMoreByStudio">
    @if ($moreByStudioCount)
        <section id="moreByStudio" class="pt-5 pb-8 px-4 border-t-2">
            <x-section-nav>
                <x-slot:title>
                    {{ __('More By :x', ['x' => $studio->name]) }}
                </x-slot>

                <x-slot:action>
                    @hasrole('superAdmin')
                        <x-button wire:click="loadMoreByStudio">{{ __('Refresh') }}</x-button>
                    @endhasrole
                    <x-section-nav-link href="{{ route('studios.details', $studio) }}">{{ __('See All') }}</x-section-nav-link>
                </x-slot>
            </x-section-nav>

            <div class="flex justify-center">
                <x-spinner />
            </div>

            <x-rows.small-lockup :animes="$moreByStudio" />
        </section>
    @endif
</div>
