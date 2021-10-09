@props(['disabled' => false])

<textarea {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'form-text w-full rounded-md shadow-sm']) !!}>{{ $slot }}</textarea>
