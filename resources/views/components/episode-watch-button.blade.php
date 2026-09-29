@props(['episode'])

@php
    $watchButton = [
        'id' => $episode->id,
        'watched' => (bool) ($episode->isWatched ?? false),
        'authenticated' => auth()->check(),
        'signInUrl' => route('sign-in'),
    ];
@endphp

<div
    class="inline-block relative"
    x-data="episodeWatchButton({{ Js::from($watchButton) }})"
    x-on:episode-watched.window="sync($event.detail)"
    x-on:user-actions-failed.window="busy = false"
>
    <x-tinted-pill-button
        color="orange"
        :title="$watchButton['watched'] ? __('Mark as Unwatched') : __('Mark as Watched')"
        x-bind:title="watched ? {{ Js::from(__('Mark as Unwatched')) }} : {{ Js::from(__('Mark as Watched')) }}"
        x-on:click="toggle()"
        x-bind:disabled="busy"
    >
        <span class="inline-flex items-center gap-1" x-show="watched" @if (!$watchButton['watched']) style="display: none;" @endif>
            @svg('checkmark', 'fill-current', ['width' => 12])
            {{ __('Watched') }}
        </span>

        <span x-show="!watched" @if ($watchButton['watched']) style="display: none;" @endif>{{ __('Mark as Watched') }}</span>
    </x-tinted-pill-button>
</div>
