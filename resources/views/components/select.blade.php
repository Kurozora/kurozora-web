@props(['disabled' => false])

<select {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'form-select w-full rounded-md shadow-sm']) !!}>
    {{ $slot }}
</select>
