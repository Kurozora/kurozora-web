@props(['href', 'title', 'backgroundColor' => 'transparent', 'backgroundImage' => ''])

<a href="{{ $href }}" class="w-[300px] sm:w-[350px] pb-2">
    <div class="flex justify-center w-full h-[150px] sm:h-[200px] bg-no-repeat bg-contain bg-center rounded-lg border-2 border-solid border-black/5" style="background-color: {{ $backgroundColor }};">
        <picture class="relative">
            <img class="h-[150px] sm:h-[200px] lazyload" data-sizes="auto" data-src="{{ $backgroundImage }}" alt="{{ $title }} Symbol" title="{{ $title }}">
        </picture>
    </div>

    @if(!empty($title))
        <p class="pt-3">{{ $title }}</p>
    @endif
</a>
