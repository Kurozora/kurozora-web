@props(['shareKey', 'label' => __('Share')])

<x-circle-button
    {{ $attributes->merge(['class' => 'group aria-busy:cursor-wait']) }}
    color="glass"
    data-recap-share="{{ $shareKey }}"
    aria-label="{{ $label }}"
    title="{{ $label }}"
>
    <span class="flex group-aria-busy:hidden" aria-hidden="true">
        @svg('square_and_arrow_up_fill', 'fill-current', ['width' => 14])
    </span>

    <span class="hidden group-aria-busy:flex" aria-hidden="true">
        <x-spinner variant="secondary" :wire-loading-enabled="false" width="16" />
    </span>
</x-circle-button>
