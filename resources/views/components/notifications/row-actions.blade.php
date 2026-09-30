@props(['notification'])

@php
    /** @var \App\Models\Notification $notification */
    $markLabel = $notification->isUnread() ? __('Mark as read') : __('Mark as unread');
@endphp

<div class="flex items-center gap-3 pl-2 pr-2 pt-1 pb-1 bg-secondary border border-primary rounded-md shadow-sm">
    <button
        type="button"
        class="text-xs text-tint hover:opacity-75 cursor-pointer"
        x-on:click.stop="setRead([{{ Js::from($notification->id) }}], {{ $notification->isUnread() ? 'true' : 'false' }})"
    >
        {{ $markLabel }}
    </button>

    <button
        type="button"
        class="text-xs text-red-500 hover:opacity-75 cursor-pointer"
        x-on:click.stop="confirmDelete([{{ Js::from($notification->id) }}])"
    >
        {{ __('Delete') }}
    </button>
</div>
