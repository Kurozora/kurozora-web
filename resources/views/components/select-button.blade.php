@props(['disabled' => false, 'chevronClass' => 'btn-text-tinted', 'chevronStrokeWidth' => '2', 'rounded' => 'md'])

@php
    $rounded = match ($rounded) {
        'full' => 'rounded-full',
        default => 'rounded-md'
    }
@endphp

<div class="inline-block relative">
    <select {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'form-select bg-none w-full border border-transparent shadow-sm ' . $rounded . ' focus:border-tint focus:ring-tint disabled:border-gray-300']) !!}>
        {{ $slot }}
    </select>

    <div class="absolute inset-y-0 right-0 flex items-center pr-1 pl-1 pointer-events-none">
        @svg('chevron_down_stroke', 'stroke-current ' . $chevronClass, ['fill' => 'none', 'width' => 24, 'stroke-width' => $chevronStrokeWidth])
    </div>
</div>
