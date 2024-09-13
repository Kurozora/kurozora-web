@props(['recap', 'title', 'progressAriaLabel', 'progressCount', 'progressUnit', 'mediaCollection' => \App\Enums\MediaCollection::Poster])

<div
    {{ $attributes->merge(['class' => 'flex flex-col items-center justify-between gap-6 w-full pt-5 pl-5 pb-5 pr-5 bg-gray-200/20 rounded-xl overflow-hidden', 'style' => 'min-width: 256px; max-width: 384px;']) }}
    wire:key="{{ uniqid($recap?->id, true) }}"
>
    <div class="w-full">
        <h2 class="text-2xl font-semibold">{{ __('Milestone') }}</h2>
        <p class="text-2xl opacity-75 font-semibold">{{ $title }}</p>
    </div>

    <div class="relative aspect-square" style="height: 232px" role="progressbar" aria-label="{{ $progressAriaLabel }}">
        <svg id="svg-{{ rand(3, 3) }}" class="absolute w-full h-full" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 865 862" width="865" height="862" preserveAspectRatio="xMinYMin none">
            <mask id="path-1-inside-1_1454_36805" fill="#ffffff">
                <path id="path4096"
                      d="M864.462 430.852c0 237.953-193.517 430.852-432.231 430.852C193.516 861.704 0 668.805 0 430.852 0 192.899 193.516 0 432.231 0c238.714 0 432.231 192.899 432.231 430.852Zm-803.9497 0c0 204.64 166.4237 370.533 371.7187 370.533 205.294 0 371.718-165.893 371.718-370.533 0-204.64-166.424-370.5327-371.718-370.5327-205.295 0-371.7187 165.8927-371.7187 370.5327Z" />
            </mask>
            <path id="path4099" stroke="#ff9300" stroke-linejoin="round" stroke-width="119.589"
                  d="M864.462 430.852c0 237.953-193.517 430.852-432.231 430.852C193.516 861.704 0 668.805 0 430.852 0 192.899 193.516 0 432.231 0c238.714 0 432.231 192.899 432.231 430.852Zm-803.9497 0c0 204.64 166.4237 370.533 371.7187 370.533 205.294 0 371.718-165.893 371.718-370.533 0-204.64-166.424-370.5327-371.718-370.5327-205.295 0-371.7187 165.8927-371.7187 370.5327z"
                  mask="url(#path-1-inside-1_1454_36805)"
                  style="display: inline; stroke: #ff9300; stroke-opacity: 1;" />
            <mask id="mask0_1454_36805" width="865" height="862" x="0" y="0" maskUnits="userSpaceOnUse"
                  style="mask-type: alpha;">
                <mask id="path-2-inside-2_1454_36805" fill="#ff9300">
                    <path id="path4101"
                          d="M864.462 430.852c0 237.953-193.517 430.852-432.231 430.852C193.516 861.704 0 668.805 0 430.852 0 192.899 193.516 0 432.231 0c238.714 0 432.231 192.899 432.231 430.852Zm-803.9497 0c0 204.64 166.4237 370.533 371.7187 370.533 205.294 0 371.718-165.893 371.718-370.533 0-204.64-166.424-370.5327-371.718-370.5327-205.295 0-371.7187 165.8927-371.7187 370.5327Z" />
                </mask>
                <path id="path4104" stroke="#ff9300" stroke-linejoin="round" stroke-width="119.589"
                      d="M864.462 430.852c0 237.953-193.517 430.852-432.231 430.852C193.516 861.704 0 668.805 0 430.852 0 192.899 193.516 0 432.231 0c238.714 0 432.231 192.899 432.231 430.852Zm-803.9497 0c0 204.64 166.4237 370.533 371.7187 370.533 205.294 0 371.718-165.893 371.718-370.533 0-204.64-166.424-370.5327-371.718-370.5327-205.295 0-371.7187 165.8927-371.7187 370.5327Z"
                      mask="url(#path-2-inside-2_1454_36805)" style="stroke: #ff9300; stroke-opacity: 1;" />
            </mask>
            <g id="g4114" mask="url(#mask0_1454_36805)">
                <g id="g4112" filter="url(#filter0_d_1454_36805)">
                    <mask id="path-3-inside-3_1454_36805" fill="#ff9300">
                        <path id="path4107"
                              d="M633.222 83.8497c8.355-14.4251 26.911-19.4647 40.741-10.1644C752.516 126.507 811.734 203.797 842.038 293.875c33.515 99.625 29.351 208.024-11.707 304.802-41.059 96.778-116.182 175.264-211.235 220.691-95.053 45.426-203.483 54.662-304.89 25.969C212.8 816.644 125.407 752 68.4726 663.568 11.5377 575.137-11.0149 469.013 5.05795 365.165 21.1308 261.317 74.7212 166.904 155.746 99.6883 229.012 38.9098 320.142 4.15851 414.826.356495 431.494-.312791 444.612 13.6683 444.118 30.302c-.494 16.6651-14.469 29.6592-31.159 30.5227-79.883 4.1328-156.607 33.8797-218.505 85.2273-69.681 57.805-115.7686 139.001-129.5913 228.31-13.8227 89.31 5.5726 180.576 54.5363 256.627C168.363 707.04 243.52 762.634 330.73 787.31c87.21 24.676 180.46 16.734 262.206-22.333 81.745-39.067 146.351-106.565 181.661-189.794 35.31-83.23 38.891-176.453 10.068-262.13-25.609-76.126-75.232-141.632-141.05-186.985-13.724-9.457-18.748-27.7932-10.393-42.2183Z" />
                    </mask>
                    <path id="path4110" stroke="url(#paint0_linear_1454_36805)" stroke-linejoin="round"
                          stroke-width="119.589"
                          d="M633.222 83.8497c8.355-14.4251 26.911-19.4647 40.741-10.1644C752.516 126.507 811.734 203.797 842.038 293.875c33.515 99.625 29.351 208.024-11.707 304.802-41.059 96.778-116.182 175.264-211.235 220.691-95.053 45.426-203.483 54.662-304.89 25.969C212.8 816.644 125.407 752 68.4726 663.568 11.5377 575.137-11.0149 469.013 5.05795 365.165 21.1308 261.317 74.7212 166.904 155.746 99.6883 229.012 38.9098 320.142 4.15851 414.826.356495 431.494-.312791 444.612 13.6683 444.118 30.302c-.494 16.6651-14.469 29.6592-31.159 30.5227-79.883 4.1328-156.607 33.8797-218.505 85.2273-69.681 57.805-115.7686 139.001-129.5913 228.31-13.8227 89.31 5.5726 180.576 54.5363 256.627C168.363 707.04 243.52 762.634 330.73 787.31c87.21 24.676 180.46 16.734 262.206-22.333 81.745-39.067 146.351-106.565 181.661-189.794 35.31-83.23 38.891-176.453 10.068-262.13-25.609-76.126-75.232-141.632-141.05-186.985-13.724-9.457-18.748-27.7932-10.393-42.2183Z"
                          mask="url(#path-3-inside-3_1454_36805)" shape-rendering="crispEdges"
                          style="stroke: url(&quot;#paint0_linear_1454_36805&quot;);" />
                </g>
            </g>
            <defs id="defs4138">
                <linearGradient id="paint0_linear_1454_36805" x1="648.07965" x2="490.547" y1=".32203391" y2="132.622"
                                gradientUnits="userSpaceOnUse">
                    <stop id="stop4133" offset="0" stop-color="#ff9300" stop-opacity="0"></stop>
                    <stop id="stop4135" offset="1" stop-color="#ff9300" stop-opacity="1"></stop>
                </linearGradient>
                <filter id="filter0_d_1454_36805" width="922.344" height="919.26" x="-7.85433" y="-28.6087"
                        color-interpolation-filters="sRGB" filterUnits="userSpaceOnUse">
                    <feFlood id="feFlood4116" flood-opacity="0" result="BackgroundImageFix"></feFlood>
                    <feColorMatrix id="feColorMatrix4118" in="SourceAlpha" result="hardAlpha" type="matrix"
                                   values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"></feColorMatrix>
                    <feOffset id="feOffset4120" dx="21.0844"></feOffset>
                    <feGaussianBlur id="feGaussianBlur4122" stdDeviation="14.4704"></feGaussianBlur>
                    <feComposite id="feComposite4124" in2="hardAlpha" operator="out"></feComposite>
                    <feColorMatrix id="feColorMatrix4126" type="matrix"
                                   values="0 0 0 0 0.573381 0 0 0 0 0.373381 0 0 0 0 0.373381 0 0 0 1 0"></feColorMatrix>
                    <feBlend id="feBlend4128" in2="BackgroundImageFix" mode="normal"
                             result="effect1_dropShadow_1454_36805"></feBlend>
                    <feBlend id="feBlend4130" in="SourceGraphic" in2="effect1_dropShadow_1454_36805" mode="normal"
                             result="shape"></feBlend>
                </filter>
            </defs>
        </svg>

        <div class="flex flex-col items-center justify-center h-full pl-5 pr-5">
            <p class="text-2xl font-semibold">{{ $progressCount }}</p>
            <p class="font-semibold">{{ $progressUnit }}</p>
        </div>
    </div>

    @if (!empty($recap))
        @switch($recap->type)
            @case(\App\Models\Anime::class)
                @switch ($mediaCollection)
                    @case('poster')
                        <div class="flex justify-center gap-4 h-40" style="width: 147%;">
                            @foreach ($recap->recapItems->concat($recap->recapItems)->take(4)->pad(4, $recap->recapItems->last()) as $key => $recapItem)
                                <a class="flex w-1/3 mt-auto" href="{{ route('anime.details', $recapItem->model) }}" wire:navigate style="min-height: 108px; aspect-ratio: 3/4.23;">
                                    <picture
                                        class="relative w-full rounded-lg overflow-hidden"
                                        style="background-color: {{ $recapItem->model->getFirstMedia(\App\Enums\MediaCollection::Poster)?->custom_properties['background_color'] ?? '#000000' }};"
                                    >
                                        <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $recapItem->model->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_poster.webp') }}" alt="{{ $recapItem->model->title }} Poster" title="{{ $recapItem->model->title }}">

                                        <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-lg"></div>
                                    </picture>
                                </a>
                            @endforeach
                        </div>
                        @break
                    @case('banner')
                        <div class="flex justify-center gap-4 h-40" style="max-width: 260%; width: 100vw;">
                            @foreach ($recap->recapItems->reverse()->concat($recap->recapItems->reverse())->take(3)->pad(3, $recap->recapItems->last()) as $key => $recapItem)
                                <a class="flex w-1/3 mt-auto aspect-video" href="{{ route('anime.details', $recapItem->model) }}" wire:navigate style="min-height: 108px;">
                                    <picture
                                        class="relative w-full rounded-lg overflow-hidden"
                                        style="background-color: {{ $recapItem->model->getFirstMedia(\App\Enums\MediaCollection::Banner)?->custom_properties['background_color'] ?? '#000000' }};"
                                    >
                                        <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $recapItem->model->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? $recapItem->model->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_banner.webp') }}" alt="{{ $recapItem->model->title }} Banner" title="{{ $recapItem->model->title }}">
                                        <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-lg"></div>
                                    </picture>
                                </a>
                            @endforeach
                        </div>
                        @break
                    @default
                @endswitch
                @break
            @case(\App\Models\Game::class)
                <div class="flex justify-center gap-4 h-40" style="width: 150%;">
                    @foreach ($recap->recapItems->concat($recap->recapItems)->take(3)->pad(3, $recap->recapItems->last()) as $key => $recapItem)
                        <a class="flex w-1/3 mt-auto aspect-square" href="{{ route('games.details', $recapItem->model) }}" wire:navigate style="min-height: 108px;">
                            <picture
                                class="relative aspect-square rounded-3xl overflow-hidden"
                                style="background-color: {{ $recapItem->model->getFirstMedia(\App\Enums\MediaCollection::Poster)?->custom_properties['background_color'] ?? '#000000' }};"
                            >
                                <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $recapItem->model->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_poster.webp') }}" alt="{{ $recapItem->model->title }} Poster" title="{{ $recapItem->model->title }}">

                                <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-3xl"></div>
                            </picture>
                        </a>
                    @endforeach
                </div>
                @break
            @case(\App\Models\Manga::class)
{{--                <div class="flex justify-center gap-4 h-40" style="width: 100%;">--}}
{{--                    @foreach ($recap->recapItems->reverse()->concat($recap)->take(4)->pad(4, $recap->recapItems->last()) as $key => $recapItem)--}}
{{--                        <a class="flex w-1/3 mt-auto" href="{{ route('manga.details', $recapItem->model) }}" wire:navigate style="min-height: 108px; aspect-ratio: 3/4.23;">--}}
{{--                            <svg class="relative overflow-hidden">--}}
{{--                                <foreignObject width="112" height="160" mask="url(#svg-mask-book-cover)">--}}
{{--                                    <img class="h-full w-full object-cover lazyload" data-sizes="auto" data-src="{{ $recapItem->model->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_poster.webp') }}" alt="{{ $recapItem->model->title }} Poster" title="{{ $recapItem->model->title }}" />--}}
{{--                                </foreignObject>--}}

{{--                                <g opacity="0.40">--}}
{{--                                    <use fill-opacity="0.03" fill="url(#svg-pattern-book-cover-1)" fill-rule="evenodd" xlink:href="#svg-rect-book-cover" />--}}
{{--                                    <use fill-opacity="1" fill="url(#svg-linearGradient-book-cover-1)" fill-rule="evenodd" style="mix-blend-mode: lighten;" xlink:href="#svg-rect-book-cover" />--}}
{{--                                    <use fill-opacity="1" fill="black" filter="url(#svg-filter-book-cover-1)" xlink:href="#svg-rect-book-cover" />--}}
{{--                                </g>--}}
{{--                            </svg>--}}
{{--                        </a>--}}
{{--                    @endforeach--}}
{{--                </div>--}}
                    <div class="flex justify-center gap-4 h-40" style="width: 147%;">
                        @foreach ($recap->recapItems->concat($recap->recapItems)->take(4)->pad(4, $recap->recapItems->last()) as $key => $recapItem)
                            <a class="flex w-1/3 mt-auto" href="{{ route('anime.details', $recapItem->model) }}" wire:navigate style="min-height: 108px; aspect-ratio: 3/4.23;">
                                <picture
                                    class="relative w-full rounded-lg overflow-hidden"
                                    style="background-color: {{ $recapItem->model->getFirstMedia(\App\Enums\MediaCollection::Poster)?->custom_properties['background_color'] ?? '#000000' }};"
                                >
                                    <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $recapItem->model->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_poster.webp') }}" alt="{{ $recapItem->model->title }} Poster" title="{{ $recapItem->model->title }}">

                                    <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-lg"></div>
                                </picture>
                            </a>
                        @endforeach
                    </div>
                @break
            @default
       @endswitch
    @endif
</div>
