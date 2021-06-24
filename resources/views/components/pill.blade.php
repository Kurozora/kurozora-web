@props(['color'])

<div {{ $attributes->merge(['class' => 'p-2 text-white text-center font-bold rounded-full whitespace-nowrap']) }} style="background-color: {{ $color ?? '#71717A' }};">{{ $slot }}</div>
