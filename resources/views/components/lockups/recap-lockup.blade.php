@props(['recap', 'isRow' => true])

@php
    $class = $isRow ? 'shrink-0' : 'flex-grow';
@endphp

<div wire:key="{{ uniqid(more_entropy: true) }}" class="relative pb-2 w-64 snap-normal snap-center {{ $class }}">
    <div class="relative flex flex-col">
        <x-picture class="aspect-square rounded-lg shadow-md overflow-hidden">
            <img class="h-full w-full blur object-cover" style="transform: scale(1.1)"  src="{{ asset('images/static/star_bg_md.jpg') }}" alt="" width="320" height="320">

            <div
                class="absolute top-0 left-0 h-full w-full"
                style="background: linear-gradient({{ $recap->background_color1 }}, {{ $recap->background_color2 }}); opacity: 50%"
            ></div>

            <div class="absolute top-0 left-0 h-full w-full">
                <div class="flex flex-row items-center justify-between pt-1 pr-2 pb-2 pl-2 text-white">
                    <p class="text-2xl font-bold">{{ __('Re:CAP') }}</p>

                    <x-logo class="h-6 w-auto" />
                </div>

                <p class="absolute bottom-0 text-white font-black" style="font-size: 192px;top: 50%;left: 50%;transform: translate(-50%, -50%);letter-spacing: -12px;opacity: 92%;">
                    {{ __('’:x', ['x' => substr($recap->year, -2)]) }}
                </p>
            </div>

            <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-lg"></div>
        </x-picture>

        <a class="absolute h-full w-full" href="{{ route('recap.index', ['year' => $recap->year]) }}" wire:navigate></a>
    </div>
</div>
