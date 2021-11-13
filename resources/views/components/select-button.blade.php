@props(['disabled' => false, 'chevronColor' => 'text-white', 'chevronStrokeWidth' => '2'])

<div class="inline-block relative">
    <select {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'form-select bg-none w-full rounded-md shadow-sm']) !!}>
        {{ $slot }}
    </select>

    <div class="absolute inset-y-0 right-0 flex items-center px-1 pointer-events-none">
        <svg class="stroke-current h-6 w-6 {{ $chevronColor }}" fill='none' viewBox='0 0 20 20' xmlns='http://www.w3.org/2000/svg'>
            <path stroke-linecap='round' stroke-linejoin='round' stroke-width="{{ $chevronStrokeWidth }}" d='M6 8l4 4 4-4'/>
        </svg>
    </div>
</div>
