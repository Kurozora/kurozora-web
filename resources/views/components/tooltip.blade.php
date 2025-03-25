@props(['id'])

@php
    $id = $id ?? md5($attributes->wire('model'));
@endphp

<div
    {{ $attributes->merge(['class' => 'absolute z-50']) }}
    id="{{ $id }}"
    x-cloak
    x-transition:enter="ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
>
    <div class="flex gap-1 pl-2 pt-2 pb-2 pr-2 w-64 bg-primary border border-black/20 shadow-xl rounded-md">
        {{ $slot }}
    </div>
</div>
