@props(['color'])

<div {{ $attributes->merge(['class' => 'pt-2 pr-2 pb-2 pl-2 text-white text-center font-bold rounded-full whitespace-nowrap']) }} style="background-color: {{ $color ?? '#71717A' }};">{{ $slot }}</div>
