@props(['selected' => false, 'disabled' => false])

@php
    $stateClasses = $selected
        ? 'bg-tint btn-text-tinted border-transparent'
        : 'bg-primary text-tint border-tint';
@endphp

<button {{ $attributes->merge([
    'type' => 'button',
    'aria-pressed' => $selected ? 'true' : 'false',
    'class' => 'inline-flex items-center justify-center pl-2 pr-2 pt-1 pb-1 border rounded-md text-xs font-semibold uppercase tracking-widest shadow-sm transition ease-in-out duration-150 hover:bg-tint-800 hover:btn-text-tinted focus:border-tint focus:ring-tint disabled:bg-gray-200 disabled:border-gray-200 disabled:text-gray-300 disabled:cursor-default sm:px-4 sm:py-2 ' . $stateClasses,
]) }} {{ $disabled ? 'disabled' : '' }}>
    {{ $slot }}
</button>
