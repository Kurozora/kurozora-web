@props(['icon' => null, 'label'])

@php
    $hasIcon = $icon && file_exists(public_path("images/symbols/$icon.svg"));
@endphp

<div class="relative" data-submenu>
    <button type="button" data-submenu-trigger class="flex items-center gap-2 w-full pl-2 pr-2 pt-1.5 pb-1.5 text-xs text-left rounded-lg focus:outline-none">
        @if ($hasIcon)
            <span class="shrink-0">
                @svg($icon, 'fill-current', ['width' => 12])
            </span>
        @endif

        <span class="flex-grow whitespace-nowrap">{{ $label }}</span>

        <span class="shrink-0">
            @svg('chevron_forward', 'fill-current', ['width' => 10])
        </span>
    </button>

    <div data-submenu-flyout class="invisible opacity-0 transition-opacity duration-150 absolute z-10 p-1 w-max max-w-xs rounded-xl border border-primary shadow-xl bg-secondary">
        <button type="button" data-submenu-header class="hidden items-center gap-2 w-full pl-2 pr-2 pt-1.5 pb-1.5 text-xs text-left rounded-lg">
            @if ($hasIcon)
                <span class="shrink-0">
                    @svg($icon, 'fill-current', ['width' => 12])
                </span>
            @endif

            <span class="flex-grow whitespace-nowrap">{{ $label }}</span>

            <span data-submenu-chevron class="shrink-0 rotate-90">
                @svg('chevron_forward', 'fill-current', ['width' => 10])
            </span>
        </button>

        <x-hr data-overlay-divider class="hidden my-1" />

        {{ $slot }}
    </div>
</div>
