<x-permission-prompt
    id="music-connect-prompt"
    :image="asset('images/brands/apple_music.webp')"
    :badge="asset('images/static/icon/app_icon.webp')"
>
    <x-slot:title>
        {{ __('“:app” Would Like to Access Apple Music', ['app' => config('app.name')]) }}
    </x-slot:title>

    <x-slot:message>
        {{ __(':app requires access to your Apple Music library so you can add anime songs directly from within the website. You can also listen to the full version of the songs instead of the preview version.', ['app' => config('app.name')]) }}
    </x-slot:message>

    <x-slot:actions>
        <button type="button" data-music-dismiss class="inline-flex items-center justify-center gap-1 pl-2 pr-2 pt-1 pb-1 h-8 text-sm rounded-full bg-tertiary text-primary transition hover:opacity-90 sm:h-10">
            {{ __('Don’t Allow') }}
        </button>

        <button type="button" data-music-connect class="inline-flex items-center justify-center gap-1 pl-2 pr-2 pt-1 pb-1 h-8 text-sm rounded-full bg-tertiary text-primary transition hover:opacity-90 sm:h-10">
            {{ __('Allow') }}
        </button>
    </x-slot:actions>
</x-permission-prompt>
