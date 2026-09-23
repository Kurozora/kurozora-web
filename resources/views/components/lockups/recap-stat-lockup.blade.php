@props(['title', 'value', 'caption' => null])

<div {{ $attributes->merge(['class' => 'relative flex flex-col justify-between gap-6 w-full shrink-0 pt-4 pl-4 pb-4 pr-4 bg-secondary rounded-xl shadow-md overflow-hidden snap-start', 'style' => 'min-width: 256px; max-width: 384px;']) }}>
    <h3 class="text-2xl text-secondary font-semibold">{{ $title }}</h3>

    <div>
        <p class="text-4xl font-bold leading-tight line-clamp-2" title="{{ $value }}">{{ $value }}</p>

        @if (!empty($caption))
            <p class="mt-1 text-lg text-secondary font-semibold">{{ $caption }}</p>
        @endif
    </div>

    <div class="absolute top-0 left-0 h-full w-full border border-solid border-primary rounded-xl pointer-events-none"></div>
</div>
