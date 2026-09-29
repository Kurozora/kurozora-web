@props(['themeId', 'name'])

@php
    $themeId = (string) $themeId;
    $isDefaultTheme = $themeId === 'kurozora';
@endphp

<div
    class="inline-block relative"
    x-data="themeGetButton({{ Js::from(['id' => $themeId]) }})"
    x-on:theme-changed.window="sync($event.detail)"
    x-on:present-subscription-sheet.window="busy = false"
    x-on:user-actions-failed.window="busy = false"
>
    <x-tinted-pill-button
        color="orange"
        :title="$isDefaultTheme ? __('Using ‘:x’ theme', ['x' => $name]) : __('Get ‘:x’ theme', ['x' => $name])"
        x-bind:title="currentThemeID === {{ Js::from($themeId) }} ? {{ Js::from(__('Using ‘:x’ theme', ['x' => $name])) }} : {{ Js::from(__('Get ‘:x’ theme', ['x' => $name])) }}"
        x-on:click="get()"
        x-bind:disabled="busy"
    >
        <span x-text="currentThemeID === {{ Js::from($themeId) }} ? {{ Js::from(__('USING')) }} : {{ Js::from(__('GET')) }}">{{ $isDefaultTheme ? __('USING') : __('GET') }}</span>
    </x-tinted-pill-button>
</div>
