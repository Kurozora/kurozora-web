<x-base-layout>
    <x-slot:title>
        {{ $title }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Guess the hidden anime word in six tries.') }}
    </x-slot:description>

    <x-slot:meta>
        @if($indexable)
            <meta property="og:title" content="{{ $title }} — {{ config('app.name') }}" />
            <meta property="og:description" content="{{ __('Guess the hidden anime word in six tries.') }}" />
            <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
            <meta property="og:type" content="website" />
        @else
            <meta name="robots" content="noindex" />
        @endif

        <link rel="canonical" href="{{ $canonicalUrl }}">
    </x-slot:meta>

    <x-slot:appArgument>
        {{ $appArgument }}
    </x-slot:appArgument>

    <main>
        @if($mode->is(\App\Enums\Minigames\Kotodama\GameMode::Archive))
            <x-back-link :url="route('kotodama.archive')" :label="__('Kotodama Archive')" :title="$title" />
        @else
            <x-back-link :url="route('kotodama.daily')" :label="__('Kotodama')" :title="$title" />
        @endif

        <div class="pb-10">
            <livewire:components.kotodama-puzzle :game-id="$game?->id" :mode="$mode->value" :flash="$flash" />
        </div>
    </main>
</x-base-layout>
