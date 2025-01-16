@props(['disabled' => false])

<label class="inline-flex items-center">
    <input type="checkbox" {{ $attributes->merge(['class' => 'form-checkbox h-4 w-4 text-tint rounded shadow-sm focus:border-tint focus:ring-tint']) }} {{ $disabled ? 'disabled' : '' }} />
    <span class="ml-2 whitespace-nowrap">{{ $slot ?? '' }}</span>
</label>
