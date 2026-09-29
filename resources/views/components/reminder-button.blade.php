@props(['anime'])

@php
    $reminderButton = [
        'id' => $anime->id,
        'reminded' => (bool) $anime->isReminded,
        'authenticated' => auth()->check(),
        'signUpUrl' => route('sign-up'),
    ];
@endphp

<button
    @class([
        'drop-shadow inline-flex justify-center items-center pl-4 pr-4 pt-2 pb-2 font-semibold text-xs uppercase tracking-widest border-0 rounded-full shadow-md focus:ring-0 disabled:backdrop-filter disabled:bg-white/80 disabled:backdrop-blur disabled:text-gray-400 disabled:cursor-default',
        'bg-white text-gray-500 disabled:opacity-100' => $reminderButton['reminded'],
        'bg-tint btn-text-tinted hover:bg-tint-800 active:bg-tint' => !$reminderButton['reminded'],
    ])
    style="min-width: 100px;"
    x-data="reminderButton({{ Js::from($reminderButton) }})"
    x-on:anime-reminded.window="sync($event.detail)"
    x-on:present-subscription-sheet.window="busy = false"
    x-on:user-actions-failed.window="busy = false"
    x-bind:class="{ 'bg-white text-gray-500 disabled:opacity-100': reminded, 'bg-tint btn-text-tinted hover:bg-tint-800 active:bg-tint': !reminded }"
    x-bind:disabled="reminded || busy"
    x-on:click="remind()"
    @if ($reminderButton['reminded']) disabled @endif
>
    <span x-show="reminded" @if (!$reminderButton['reminded']) style="display: none;" @endif>
        @svg('checkmark', 'fill-current', ['width' => '16'])
    </span>

    <span x-show="!reminded" @if ($reminderButton['reminded']) style="display: none;" @endif>{{ __('Remind Me') }}</span>
</button>
