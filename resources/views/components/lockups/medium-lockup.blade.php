@props(['href', 'title', 'backgroundColor' => 'transparent', 'backgroundImage' => ''])

<a href="{{ $href }}" class="relative pb-2 snap-normal snap-center">
    <div class="flex justify-center w-64 h-40 rounded-lg border-2 border-solid border-black/5" style="background-color: {{ $backgroundColor }};">
        <picture class="relative">
            <img class="h-full m-auto lazyload" data-sizes="auto" data-src="{{ $backgroundImage }}" alt="{{ $title }} Symbol" title="{{ $title }}">
        </picture>
    </div>

    @if(!empty($title))
        <p class="pt-3">{{ $title }}</p>
    @endif
</a>
