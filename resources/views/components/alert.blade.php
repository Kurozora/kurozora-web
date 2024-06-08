@props(['message', 'isEphemeral' => true, 'type' => 'success'])

@php
    $colorCSS = match($type) {
        'warning' => 'bg-yellow-500',
        'error' => 'bg-red-500',
        default => 'bg-green-500'
    };
    $svgName = match($type) {
        'warning' => 'exclamationmark_circle',
        'error' => 'xmark_circle',
        default => 'checkmark_circle'
    };
@endphp

<div
    x-data="{
        isEphemeral: @json($isEphemeral),
        openAlertBox: true
    }"
    x-init="setTimeout(function () { openAlertBox = !isEphemeral }, 2500)"
>
    <div
        class="fixed top-0 right-0 py-16 pl-4 pr-4 z-[999]"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-show="openAlertBox"
    >
        <div class="flex items-center gap-2 {{ $colorCSS }} text-white text-sm pl-4 pr-4 pt-3 pb-3 rounded shadow-md" role="alert">
            @svg($svgName, 'fill-current', ['width' => 20])

            <span class="flex">{{ $message }}</span>

            @if ($isEphemeral)
                <button type="button" class="flex" @click="openAlertBox = false">
                    @svg('xmark', 'fill-current', ['width' => 16])
                </button>
            @endif
        </div>
    </div>
</div>
