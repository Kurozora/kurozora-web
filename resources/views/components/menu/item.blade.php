@props(['icon' => null, 'href' => null])

@php
    $itemClasses = 'flex items-center gap-2 w-full pl-2 pr-2 pt-1.5 pb-1.5 text-xs text-left rounded-lg hover:bg-tint hover:btn-text-tinted focus:bg-tint focus:btn-text-tinted focus:outline-none';
    $hasIcon = $icon && file_exists(public_path("images/symbols/$icon.svg"));
@endphp

@if ($href)
    <a href="{{ $href }}" target="_blank" data-menu-item {{ $attributes->merge(['class' => $itemClasses . ' no-external-icon']) }}>
        @if ($hasIcon)
            <span class="shrink-0">@svg($icon, 'fill-current', ['width' => 12])</span>
        @endif

        <span class="flex-grow whitespace-nowrap">{{ $slot }}</span>
    </a>
@else
    <button type="button" data-menu-item {{ $attributes->merge(['class' => $itemClasses]) }}>
        @if ($hasIcon)
            <span class="shrink-0">@svg($icon, 'fill-current', ['width' => 12])</span>
        @endif

        <span class="flex-grow whitespace-nowrap">{{ $slot }}</span>
    </button>
@endif
