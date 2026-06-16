<x-permission-prompt id="music-remove-prompt">
    <x-slot:title>
        {{ __('How to Remove') }}
    </x-slot:title>

    <x-slot:message>
        {{ __('Songs added to your Apple Music library can only be removed from within Apple Music.') }}
    </x-slot:message>

    <x-slot:actions>
        <a href="https://music.apple.com/library" target="_blank" data-music-dismiss class="inline-flex items-center justify-center gap-1 pl-2 pr-2 pt-1 pb-1 h-8 text-sm rounded-full bg-tertiary text-primary transition no-external-icon hover:opacity-90 sm:h-10">
            {{ __('Open Apple Music Library') }}
        </a>

        <button type="button" data-music-dismiss class="inline-flex items-center justify-center gap-1 pl-2 pr-2 pt-1 pb-1 h-8 text-sm rounded-full bg-tertiary text-primary transition hover:opacity-90 sm:h-10">
            {{ __('OK') }}
        </button>
    </x-slot:actions>
</x-permission-prompt>
