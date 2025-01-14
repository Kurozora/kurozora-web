@props(['disabled' => false])

<label class="inline-flex items-center">
    <input type="radio" {{ $attributes->merge(['class' => 'form-radio h-4 w-4 text-tint rounded-full shadow-sm focus:border-tint focus:ring-tint']) }} {{ $disabled ? 'disabled' : '' }} />
    <span class="ml-2 whitespace-nowrap">{{ $slot ?? '' }}</span>
</label>
