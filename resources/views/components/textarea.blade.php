@props(['disabled' => false, 'readonly' => false])

@php
    $class = $readonly
            ? 'form-text w-full border-none outline-none resize-none shadow-none overflow-hidden focus:ring-0'
            : 'form-text w-full rounded-md shadow-sm';
@endphp

<textarea
    @if ($readonly)
        x-data="{ resize: () => { $el.style.height = '5px'; $el.style.height = $el.scrollHeight + 'px' } }"
        x-init="resize()"
        @input="resize()"
        @resize.window="resize()"
    @endif
    {{ $disabled ? 'disabled' : '' }} {{ $readonly ? 'readonly' : '' }} {!! $attributes->merge(['class' => $class]) !!}
>{{ $slot }}</textarea>
