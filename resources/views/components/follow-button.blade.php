@props(['user', 'isFollowed'])

@php
    $followButton = [
        'id' => $user->id,
        'followed' => (bool) $isFollowed,
        'authenticated' => auth()->check(),
        'signInUrl' => route('sign-in'),
    ];
@endphp

<div
    x-data="followButton({{ Js::from($followButton) }})"
    x-on:user-followed.window="sync($event.detail)"
    x-on:user-actions-failed.window="busy = false"
>
    <x-tinted-pill-button
        color="orange"
        :title="$followButton['followed'] ? __('Unfollow :x', ['x' => $user->username]) : __('Follow :x', ['x' => $user->username])"
        x-bind:title="followed ? {{ Js::from(__('Unfollow :x', ['x' => $user->username])) }} : {{ Js::from(__('Follow :x', ['x' => $user->username])) }}"
        x-on:click="toggle()"
        x-bind:disabled="busy"
    >
        <span x-show="followed" @if (!$followButton['followed']) style="display: none;" @endif>{{ __('Following') }}</span>

        <span x-show="!followed" @if ($followButton['followed']) style="display: none;" @endif>{{ __('Follow') }}</span>
    </x-tinted-pill-button>
</div>
