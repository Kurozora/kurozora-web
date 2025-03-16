@props(['disabled' => false, 'width' => 'w-full'])

<select {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'form-select ' . $width . ' bg-secondary text-primary border-primary rounded-md shadow-sm focus:border-tint focus:ring-tint']) !!}>
    {{ $slot }}
</select>
