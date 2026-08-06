@props(['notification'])

@php
    /** @var \App\Models\Notification $notification */
    $markAction = $notification->isUnread() ? 'markRead' : 'markUnread';
    $markLabel = $notification->isUnread() ? __('Mark as read') : __('Mark as unread');
@endphp

<div class="flex items-center gap-3 pl-2 pr-2 pt-1 pb-1 bg-secondary border border-primary rounded-md shadow-sm">
    <button
        type="button"
        class="text-xs text-tint hover:opacity-75 cursor-pointer"
        wire:click.stop="{{ $markAction }}('{{ $notification->id }}')"
    >
        {{ $markLabel }}
    </button>

    <button
        type="button"
        class="text-xs text-red-500 hover:opacity-75 cursor-pointer"
        wire:click.stop="deleteSingle('{{ $notification->id }}')"
        wire:confirm="{{ __('This notification will be removed.') }}"
    >
        {{ __('Delete') }}
    </button>
</div>
