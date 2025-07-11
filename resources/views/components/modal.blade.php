@props(['id', 'maxWidth'])

@php
    $id = $id ?? md5($attributes->wire('model'));

    match ($maxWidth ?? '2xl') {
        'sm' => $maxWidth = 'sm:max-w-sm lg:max-w-2xl',
        'md' => $maxWidth = 'sm:max-w-md lg:max-w-3xl',
        'lg' => $maxWidth = 'sm:max-w-lg lg:max-w-4xl',
        'xl' => $maxWidth = 'sm:max-w-xl lg:max-w-5xl',
        default => $maxWidth = 'sm:max-w-2xl lg:max-w-6xl'
    };
@endphp

<div
    x-data="{
        show: @entangle($attributes->get('model')).live,
        focusables() {
            // All focusable element types...
            let selector = 'a, button, input:not([type=\'hidden\']), textarea, select, details, [tabindex]:not([tabindex=\'-1\'])'

            return [...$el.querySelectorAll(selector)]
                // All non-disabled elements...
                .filter(el => ! el.hasAttribute('disabled'))
        },
        firstFocusable() { return this.focusables()[0] },
        lastFocusable() { return this.focusables().slice(-1)[0] },
        nextFocusable() { return this.focusables()[this.nextFocusableIndex()] || this.firstFocusable() },
        prevFocusable() { return this.focusables()[this.prevFocusableIndex()] || this.lastFocusable() },
        nextFocusableIndex() { return (this.focusables().indexOf(document.activeElement) + 1) % (this.focusables().length + 1) },
        prevFocusableIndex() { return Math.max(0, this.focusables().indexOf(document.activeElement)) -1 },
    }"
    x-init="$watch('show', value => {
        if (value) {
            document.body.classList.add('overflow-y-hidden');
            {{ $attributes->has('focusable') ? 'setTimeout(() => firstFocusable().focus(), 100)' : '' }}
        } else {
            document.body.classList.remove('overflow-y-hidden');
        }
    })"
    x-on:close.stop="show = false"
    x-on:keydown.escape.window="show = false"
    x-on:keydown.tab.prevent="$event.shiftKey || nextFocusable().focus()"
    x-on:keydown.shift.tab.prevent="prevFocusable().focus()"
    x-cloak
    x-show="show"
    id="{{ $id }}"
    {{ $attributes->merge(['class' => 'fixed top-0 inset-x-0 pl-4 pr-4 pt-6 z-[999] sm:px-0 sm:flex sm:items-top sm:justify-center']) }}
>
    <div
        class="fixed inset-0 transform transition-all"
        x-show="show"
        x-on:click="show = false"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div class="absolute inset-0 bg-black opacity-75"></div>
    </div>

    <div
        class="max-h-[calc(100vh-3rem)] bg-primary rounded-lg overflow-hidden shadow-xl transform transition-all overflow-y-auto sm:w-full {{ $maxWidth }} sm:mx-auto"
        x-show="show"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
    >
        {{ $slot }}
    </div>
</div>
