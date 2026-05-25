@props(['chain' => [], 'url' => null, 'label' => null, 'title' => null])

@php
    if (!$url && count($chain) >= 2) {
        $parent = $chain[count($chain) - 2];
        $url = $parent->url;
        $label = $parent->label;
    }
@endphp

@if ($url && $label)
    <section {{ $attributes->merge(['class' => 'sticky top-0 mb-4 pt-4 pb-4 backdrop-blur bg-blur z-10 xl:safe-area-inset']) }}>
        <div class="flex flex-col gap-1 pl-4 pr-4">
            <a href="{{ $url }}" wire:navigate class="text-secondary text-sm hover:underline">
                ← {{ $label }}
            </a>

            @if ($title || isset($actions))
                <div class="flex gap-1">
                    <div class="flex flex-wrap items-center w-full">
                        @if ($title)
                            <h1 class="text-2xl font-bold">{{ $title }}</h1>
                        @endif
                    </div>

                    @isset($actions)
                        <div class="flex flex-wrap justify-end items-center gap-2 w-full">
                            {{ $actions }}
                        </div>
                    @endisset
                </div>
            @endif

            {{ $slot }}
        </div>
    </section>
@endif
