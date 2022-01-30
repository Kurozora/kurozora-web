@props(['align' => 'right', 'width' => '48', 'contentClasses' => 'bg-white'])

@php
    $alignmentClasses = match($align) {
        'left' => 'origin-top-left left-0',
        'top' => 'origin-top',
        default => 'origin-top-right right-0'
    };

    $width = match($width) {
        '48'=>'w-48'
    };
@endphp

<div class="relative" x-data="{ open: false }" @click.away="open = false" @close.stop="open = false">
    <div @click="open = ! open">
        {{ $trigger }}
    </div>

    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="absolute z-50 mt-2 {{ $width }} rounded-md shadow-lg {{ $alignmentClasses }}"
         style="display: none;">
        <div class="rounded-md border border-black/5 overflow-hidden {{ $contentClasses }}">
            {{ $content }}
        </div>
    </div>
</div>
