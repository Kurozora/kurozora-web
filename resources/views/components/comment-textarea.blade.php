@props(['autoresize' => true, 'rounded' => 'full', 'disabled' => false, 'readonly' => false])

<x-textarea
    {{ $attributes->merge(['class' => 'bg-transparent resize-none', 'rows' => 1]) }}
    placeholder="{{ __('Add a comment…') }}"
    :autoresize="$autoresize"
    :rounded="$rounded"
    :disabled="$disabled"
    :readonly="$readonly"
></x-textarea>
