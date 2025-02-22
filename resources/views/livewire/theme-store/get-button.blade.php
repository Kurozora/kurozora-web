<div
    class="inline-block relative"
    x-data="{
        currentThemeID: $persist(@entangle('currentThemeID')).as('currentThemeID')
    }"
>
    <x-tinted-pill-button
        :color="'orange'"
        title="{{ $currentThemeID ? __('Using ‘:x’ theme', ['x' => $name]) : __('Get ‘:x’ theme', ['x' => $name]) }}"
        wire:click="getTheme"
        wire:loading.attr="disabled"
    >
        @if ($currentThemeID === $themeID)
            <span>{{ __('USING') }}</span>
        @else
            <span>{{ __('GET') }}</span>
        @endif
    </x-tinted-pill-button>
</div>
