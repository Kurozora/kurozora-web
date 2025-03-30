<div
    class="inline-block relative"
    x-data="{
        currentThemeID: $persist(@entangle('currentThemeID').live).as('currentThemeID')
    }"
>
    <x-tinted-pill-button
        :color="'orange'"
        title="{{ $currentThemeID === $themeID ? __('Using ‘:x’ theme', ['x' => $name]) : __('Get ‘:x’ theme', ['x' => $name]) }}"
        wire:click="getTheme"
        wire:loading.attr="disabled"
    >
        <span x-text="currentThemeID === '{{ $themeID }}' ? '{{ __('USING') }}' : '{{ __('GET') }}'"></span>
    </x-tinted-pill-button>
</div>
