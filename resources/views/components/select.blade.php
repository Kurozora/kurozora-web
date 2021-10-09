@props(['disabled' => false])

<select {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'form-select w-full rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500']) !!}>
    {{ $slot }}
</select>
