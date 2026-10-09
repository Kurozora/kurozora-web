@props(['name', 'rating' => null, 'starSize' => 'md'])

@php
    $rating = (float) ($rating ?? 0);
    $starHeight = match ($starSize) {
        'sm' => 'h-4',
        'md' => 'h-6',
        default => 'h-8',
    };
@endphp

<div {{ $attributes->merge(['class' => 'flex gap-0.5 justify-center']) }}>
    @for ($starIndex = 0; $starIndex < 5; $starIndex++)
        <div class="relative">
            <label class="absolute left-0 w-1/2 overflow-hidden cursor-pointer" style="z-index: 1;">
                <input class="hidden" type="radio" name="{{ $name }}" value="{{ $starIndex + 0.5 }}" @checked($rating == $starIndex + 0.5) />

                @svg('star_rating', 'relative ' . $starHeight . ' ' . ($rating >= $starIndex + 0.5 ? 'text-tint' : 'text-transparent'), ['fill' => 'currentColor', 'stroke-width' => '1', 'stroke' => 'var(--tint-color)'])
            </label>

            <label class="cursor-pointer">
                <input class="hidden" type="radio" name="{{ $name }}" value="{{ $starIndex + 1 }}" @checked($rating == $starIndex + 1) />

                @svg('star_rating', 'relative ' . $starHeight . ' ' . ($rating >= $starIndex + 1 ? 'text-tint' : 'text-transparent'), ['fill' => 'currentColor', 'stroke-width' => '1', 'stroke' => 'var(--tint-color)'])
            </label>
        </div>
    @endfor
</div>
