@props(['display'])

@if ($display === true)
    <span class="sr-only">{{ __('Yes') }}</span>
    @svg('checkmark', 'fill-current inline-block text-tint', ['width' => 16, 'aria-hidden' => 'true'])
@elseif ($display === false)
    <span class="sr-only">{{ __('No') }}</span>
    @svg('xmark', 'fill-current inline-block text-secondary', ['width' => 12, 'aria-hidden' => 'true'])
@else
    <span class="text-secondary">{{ $display }}</span>
@endif
