@props(['recap', 'isRow' => true])

@php
    $class = $isRow ? 'shrink-0' : 'flex-grow';
    $year = __('’:x', ['x' => substr($recap->year, -2)]);
    $foreground = $recap->is_light ? 'rgb(0 0 0 / 55%)' : 'rgb(255 255 255 / 94%)';
    $bloom = $recap->is_light ? '0 0 0' : '255 255 255';
    $numerals = 'font-size: 169px; font-size: 66cqh; line-height: 1; letter-spacing: -0.045em; color: ' . $foreground . ';';
    $crescent = 'M0 35.72C34 49.32 70 43.2 100 19.4C70 37.08 34 42.52 0 35.72Z';
    $crescentBlurId = uniqid('recapCrescent');
@endphp

<div wire:key="{{ uniqid(more_entropy: true) }}" class="relative pb-2 w-64 snap-normal snap-center {{ $class }}">
    <div class="relative flex flex-col">
        <x-picture class="aspect-square rounded-lg shadow-md overflow-hidden">
            <div class="absolute top-0 left-0 h-full w-full" style="container-type: size;">
                <div
                    class="absolute top-0 left-0 h-full w-full"
                    style="background: linear-gradient(135deg, {{ $recap->background_color1 }}, {{ $recap->background_color2 }});"
                ></div>

                <div
                    class="absolute top-0 left-0 h-full w-full"
                    style="background: radial-gradient(circle at 72% 24%, rgb({{ $bloom }} / 30%), rgb({{ $bloom }} / 8%) 45%, transparent 72%);"
                ></div>

                <p
                    class="absolute top-0 left-0 h-full w-full font-bold whitespace-nowrap"
                    style="{{ $numerals }} filter: blur(0.1em); -webkit-mask-image: linear-gradient(to bottom, transparent 48%, black 93%); mask-image: linear-gradient(to bottom, transparent 48%, black 93%);"
                ><span class="absolute" style="left: -0.09em; bottom: -0.1em;">{{ $year }}</span></p>

                <p
                    class="absolute top-0 left-0 h-full w-full font-bold whitespace-nowrap"
                    style="{{ $numerals }} -webkit-mask-image: linear-gradient(to bottom, black 48%, transparent 93%); mask-image: linear-gradient(to bottom, black 48%, transparent 93%);"
                ><span class="absolute" style="left: -0.09em; bottom: -0.1em;">{{ $year }}</span></p>

                <svg class="absolute top-0 left-0 h-full w-full" viewBox="0 0 100 100" aria-hidden="true" focusable="false">
                    <filter id="{{ $crescentBlurId }}" filterUnits="userSpaceOnUse" x="-20" y="-20" width="140" height="140">
                        <feGaussianBlur stdDeviation="2" />
                    </filter>

                    <g filter="url(#{{ $crescentBlurId }})" style="isolation: isolate;">
                        <path d="{{ $crescent }}" transform="translate(0 -3.06)" fill="{{ $recap->cool_fringe_color }}" fill-opacity="0.6" style="mix-blend-mode: screen;" />
                        <path d="{{ $crescent }}" transform="translate(0 3.06)" fill="{{ $recap->warm_fringe_color }}" fill-opacity="0.55" style="mix-blend-mode: screen;" />
                        <path d="{{ $crescent }}" fill="#ffffff" fill-opacity="0.7" style="mix-blend-mode: screen;" />
                    </g>
                </svg>

                <div
                    class="absolute top-0 left-0 flex flex-row items-center justify-between pt-1 pr-2 pb-2 pl-2 w-full"
                    style="color: {{ $foreground }};"
                >
                    <p class="text-2xl font-bold">{{ __('Re:CAP') }}</p>

                    <x-logo class="h-6 w-auto" />
                </div>
            </div>

            <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-lg"></div>
        </x-picture>

        <a class="absolute h-full w-full" href="{{ route('recap.index', ['year' => $recap->year]) }}" wire:navigate></a>
    </div>
</div>
