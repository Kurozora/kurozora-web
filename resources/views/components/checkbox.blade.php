@props(['disabled' => false])

<label class="inline-flex items-center">
    <input type="checkbox" {{ $attributes->merge(['class' => 'form-checkbox h-4 w-4 text-orange-500 rounded shadow-sm focus:border-orange-500 focus:ring-orange-500']) }} {{ $disabled ? 'disabled' : '' }}>
    <span class="ml-2">{{ $slot ?? '' }}</span>
</label>
