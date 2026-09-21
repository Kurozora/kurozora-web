@props(['source', 'lazy' => false])

<div {{ $attributes->merge(['class' => 'hidden background-extension xl:block']) }} aria-hidden="true">
    <img
        class="background-extension-mirror{{ $lazy ? ' lazyload' : '' }}"
        @if ($lazy) data-sizes="auto" data-src="{{ $source }}" @else src="{{ $source }}" @endif
        alt=""
        decoding="async"
    >

    <div class="background-extension-blur"></div>
    <div class="background-extension-blur"></div>
    <div class="background-extension-blur"></div>
    <div class="background-extension-blur"></div>
</div>
