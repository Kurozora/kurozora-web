@props(['rating' => null, 'starSize' => 'md'])

@php
    $rating ??= 0;
    $starHeight = match ($starSize) {
        'sm' => 'h-4',
        'md' => 'h-6',
        default => 'h-8',
    };
@endphp

<div {{ $attributes->merge(['class' => 'flex gap-0.5 justify-center']) }}>
    @for ($starIndex = 0; $starIndex < 5; $starIndex++)
        <div class="relative">
            <div class="absolute left-0 w-1/2 overflow-hidden" style="z-index: 1;">
                @svg('star_rating', 'relative ' . $starHeight . ' ' . ($rating >= $starIndex + 0.5 ? 'text-tint' : 'text-transparent'), ['fill' => 'currentColor', 'stroke-width' => '1', 'stroke' => 'var(--tint-color)'])
            </div>

            @svg('star_rating', 'relative ' . $starHeight . ' ' . ($rating >= $starIndex + 1 ? 'text-tint' : 'text-transparent'), ['fill' => 'currentColor', 'stroke-width' => '1', 'stroke' => 'var(--tint-color)'])
        </div>
    @endfor
</div>
