@props(['disabled' => false])

<label class="inline-flex items-center">
    <input type="radio" {{ $attributes->merge(['class' => 'form-radio h-4 w-4 text-orange-500 rounded-full shadow-sm focus:border-orange-500 focus:ring-orange-500']) }} {{ $disabled ? 'disabled' : '' }} />
    <span class="ml-2 whitespace-nowrap">{{ $slot ?? '' }}</span>
</label>
