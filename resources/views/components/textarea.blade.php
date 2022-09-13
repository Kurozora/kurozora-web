@props(['autoresize' => false, 'rounded' => 'md', 'disabled' => false, 'readonly' => false])

@php
    $rounded = match($rounded) {
        'full' => 'rounded-3xl',
        default => 'rounded-md'
    };

    $class = $readonly
            ? 'form-text w-full max-h-40 border-none outline-none resize-none shadow-none overflow-hidden focus:ring-0'
            : 'form-text w-full max-h-40 ' . $rounded . ' shadow-sm focus:border-orange-500 focus:ring-orange-500';
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
            },
            reset() {
                $el.style.height = this.defaultHeight
            }
        }"
        x-bind:style="{'min-height': minHeight}"
        x-init="resize()"
        x-on:input="resize()"
        x-on:focusin="resize()"
        x-on:focusout="reset()"
    @endif
>{{ $slot }}</textarea>
