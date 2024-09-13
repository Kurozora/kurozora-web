@props(['border' => false, 'borderRoundness' => 'rounded-lg'])

<div {{ $attributes->merge(['class' => 'relative']) }}>
    <picture>
        {{ $slot }}
    </picture>

    @if ($border)
        <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 {{ $borderRoundness }}"></div>
    @endif
</div>
