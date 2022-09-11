@props(['autoresize' => true, 'rounded' => 'full', 'disabled' => false, 'readonly' => false])

<x-textarea
    {{ $attributes->merge(['class' => 'bg-transparent resize-none']) }}
    placeholder="{{ __('Add a comment...') }}"
    rows="1"
    :autoresize="$autoresize"
    :rounded="$rounded"
    :disabled="$disabled"
    :readonly="$readonly"
></x-textarea>
