@props(['disabled' => false, 'chevronClass' => 'btn-text-tinted', 'chevronStrokeWidth' => '2', 'rounded' => 'md'])

@php
    $rounded = match ($rounded) {
        'full' => 'rounded-full',
        default => 'rounded-md'
    }
@endphp

<div class="inline-block relative">
    <select {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'form-select bg-none w-full shadow-sm ' . $rounded]) !!}>
        {{ $slot }}
    </select>

    <div class="absolute inset-y-0 right-0 flex items-center pr-1 pl-1 pointer-events-none">
        <svg class="stroke-current {{ $chevronClass }}" fill='none' viewBox='0 0 20 20' xmlns='http://www.w3.org/2000/svg' width="24">
            <path stroke-linecap='round' stroke-linejoin='round' stroke-width="{{ $chevronStrokeWidth }}" d='M6 8l4 4 4-4'/>
        </svg>
    </div>
</div>
