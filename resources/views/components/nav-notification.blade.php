@php
    $hasUnreadNotifications = auth()->check() && auth()->user()->unreadNotifications()->exists();
@endphp

<a
    class="relative inline-flex h-8 w-8 items-center justify-center text-secondary cursor-pointer transition duration-150 ease-in-out hover:text-primary focus:text-primary"
    href="{{ route('notifications.index') }}"
    wire:navigate
    x-show="! isSearchEnabled"
    x-transition:enter="ease-out duration-150 delay-[350ms] transform"
    x-transition:enter-start="opacity-0 scale-75"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="ease-in duration-200 transform"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-75"
    aria-label="{{ __('Notifications') }}"
    title="{{ __('Notifications') }}"
    @auth data-nav-notification="{{ auth()->id() }}" @endauth
>
    <span
        class="absolute bg-tint aspect-square rounded-full z-1 @if (!$hasUnreadNotifications) hidden @endif"
        style="top: 0.45rem; right: 0.45rem; width: 0.40rem;"
        data-nav-notification-badge
    ></span>

    @svg('app_badge', 'fill-current', ['width' => '18'])
</a>
