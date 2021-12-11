@props(['disabled' => false, 'readonly' => false])

@php
    $class = $readonly
            ? 'form-text w-full border-none outline-none resize-none shadow-none overflow-hidden focus:ring-0'
            : 'form-text w-full rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500';
@endphp

<textarea
    rows="10"
    {{ $disabled ? 'disabled' : '' }}
    {{ $readonly ? 'readonly' : '' }}
    {!! $attributes->merge(['class' => $class]) !!}
>{{ $slot }}</textarea>
