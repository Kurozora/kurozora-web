<div wire:init="loadMoreByStudio">
    @if ($moreByStudioCount)
        <section id="moreByStudio" class="pt-5 pb-8 px-4 border-t-2">
            <x-section-nav>
                <x-slot:title>
                    {{ __('More By :x', ['x' => $studio->name]) }}
                </x-slot>

                <x-slot:action>
                    <x-section-nav-link href="{{ route('studios.details', $studio) }}">{{ __('See All') }}</x-section-nav-link>
                </x-slot>
            </x-section-nav>

            <div class="flex justify-center">
                <x-spinner />
            </div>

            <div class="grid grid-flow-col-dense gap-4 justify-start overflow-x-scroll no-scrollbar">
                @foreach($moreByStudio as $moreByStudioAnime)
                    <x-lockups.small-lockup :anime="$moreByStudioAnime" />
                @endforeach
            </div>
        </section>
    @endif
</div>
