@props(['id', 'align' => 'right', 'width' => '48', 'maxHeight' => null, 'contentClasses' => 'bg-secondary', 'overflow' => true, 'autoPlacement' => false])

@php
    $id = $id ?? md5($attributes->wire('model'));
    $alignmentClasses = match ($align) {
        'left' => 'origin-top-left left-0',
        'top' => 'origin-top',
        default => 'origin-top-right right-0'
    };
    $width = match ($width) {
        '48'=>'w-48',
        '64'=>'w-64'
    };
    $style = match ($align) {
        'top' => 'bottom: 100%;',
        default => ''
    };
@endphp

<div
    id="{{ $id }}"
    class="relative"
    x-data="dropdown({{ $autoPlacement ? 'true' : 'false' }})"
    @click.away="open = false"
    @close.stop="open = false"
    wire:key="dropdown-{{ $id }}"
>
    <div @click="toggle()" x-ref="trigger">
        {{ $trigger }}
    </div>

    <div x-show="open"
         x-ref="panel"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         {{ $attributes->merge(['class' => 'absolute z-50 mt-2 ' . $width . ' rounded-md shadow-lg ' . $alignmentClasses ]) }}
         style="display: none; {{ $style }}">
        <div
            data-dropdown-surface
            class="rounded-xl border border-primary {{ $overflow ? 'overflow-x-hidden' : '' }} {{ $maxHeight ? 'overflow-y-auto' : null }} {{ $contentClasses }}"
            style="{{ $maxHeight ? 'max-height:' . $maxHeight : null }}"
        >
            {{ $content }}
        </div>
    </div>
</div>
