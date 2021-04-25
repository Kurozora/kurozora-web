@props(['color'])

@php
    match ($color ?? 'orange') {
        'black' => $color = 'bg-black',
        'white' => $color = 'bg-white text-black ring ring-black',
        'purple' => $color = 'bg-purple-500',
        'blue' => $color = 'bg-blue-500',
        'lightBlue' => $color = 'bg-lightBlue-500',
        'green' => $color = 'bg-green-500',
        'yellow' => $color = 'bg-yellow-500',
        'amber' => $color = 'bg-amber-500',
        'orange' => $color = 'bg-orange-500',
        'red' => $color = 'bg-red-500',
        default => $color = 'bg-gray-500'
    };
@endphp

<div {{ $attributes->merge(['class' => $color . ' p-2 text-white text-center font-bold rounded-full whitespace-nowrap']) }}>{{ $slot }}</div>
