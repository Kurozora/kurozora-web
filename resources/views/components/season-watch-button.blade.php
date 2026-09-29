@props(['season'])

@php
    $watchButton = [
        'id' => $season->id,
        'watched' => auth()->user()?->hasWatchedSeason($season) ?? false,
        'authenticated' => auth()->check(),
        'signInUrl' => route('sign-in'),
    ];
@endphp

<div
    class="inline-block relative"
    x-data="seasonWatchButton({{ Js::from($watchButton) }})"
    x-on:season-watched.window="sync($event.detail)"
    x-on:user-actions-failed.window="busy = false"
>
    <x-tinted-pill-button
        color="orange"
        :title="$watchButton['watched'] ? __('Mark all episodes as unwatched') : __('Mark all episodes as watched')"
        x-bind:title="watched ? {{ Js::from(__('Mark all episodes as unwatched')) }} : {{ Js::from(__('Mark all episodes as watched')) }}"
        x-on:click="toggle()"
        x-bind:disabled="busy"
    >
        <span class="inline-flex items-center gap-1" x-show="watched" @if (!$watchButton['watched']) style="display: none;" @endif>
            @svg('checkmark', 'fill-current', ['width' => 12])
            {{ __('Watched') }}
        </span>

        <span x-show="!watched" @if ($watchButton['watched']) style="display: none;" @endif>{{ __('Mark All Watched') }}</span>
    </x-tinted-pill-button>
</div>
