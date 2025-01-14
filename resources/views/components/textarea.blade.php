@props(['autoresize' => false, 'rounded' => 'md', 'disabled' => false, 'readonly' => false])

@php
    $rounded = match ($rounded) {
        'full' => 'rounded-3xl',
        default => 'rounded-md'
    };

    $class = $readonly
            ? 'form-text w-full max-h-40 bg-secondary border-none outline-none resize-none shadow-none overflow-hidden focus:ring-0'
            : 'form-text w-full max-h-40 bg-secondary ' . $rounded . ' shadow-sm focus:border-tint focus:ring-tint';
@endphp

<textarea
    {{ $disabled ? 'disabled' : '' }}
    {{ $readonly ? 'readonly' : '' }}
    {!! $attributes->merge(['class' => $class, 'rows' => 10]) !!}

    @if ($autoresize)
        x-data="{
            minHeight: '44px',
            defaultHeight: $el.style.height,
            resize() {
                if ($el.value === '') {
                    $el.style.height = this.minHeight
                } else {
                    $el.style.height = this.defaultHeight
                }
                $el.style.height = $el.scrollHeight + 'px'
            }
        }"
        x-bind:style="{'min-height': minHeight}"
        x-init="$nextTick(() => resize())"
        x-on:input="resize()"
    @endif

    x-on:focusin="$el.classList.add('active-comment-box')"
    x-on:focusout="$el.classList.remove('active-comment-box')"
>{{ $slot }}</textarea>
