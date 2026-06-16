<x-action-section>
    <x-slot:title>
        {{ __('Apple Music') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Connect your Apple Music account to listen to the full version of songs.') }}
    </x-slot:description>

    <x-slot:content>
        <div class="max-w-xl text-sm text-primary">
            <p>{{ __('Connecting your Apple Music account lets you play full-length songs instead of previews and add anime songs to your library. An active Apple Music subscription is required.') }}</p>
        </div>

        <div class="mt-5 flex items-center gap-2 text-sm text-secondary">
            <span data-music-disconnected class="inline-flex items-center gap-2">
                @svg('xmark_circle', 'fill-current', ['width' => 18])
                {{ __('Not connected') }}
            </span>

            <span data-music-connected class="hidden inline-flex items-center gap-2">
                @svg('checkmark_circle', 'fill-current', ['width' => 18])
                {{ __('Connected') }}
            </span>
        </div>

        <div class="mt-4 flex gap-2">
            <x-button type="button" data-music-connect data-music-disconnected>
                {{ __('Connect Apple Music') }}
            </x-button>

            <x-danger-button type="button" data-music-disconnect data-music-connected class="hidden">
                {{ __('Disconnect') }}
            </x-danger-button>
        </div>
    </x-slot:content>
</x-action-section>
