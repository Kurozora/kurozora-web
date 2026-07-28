@props(['game', 'mode', 'shareText' => null, 'flash' => null])

<section
    class="flex flex-col items-center gap-2 pb-6 pl-4 pr-4 xl:safe-area-inset-scroll"
    x-data="kotodamaBoard({ length: {{ \App\Models\Minigames\Kotodama\Word::LENGTH }} })"
    x-on:keydown.window="handleKey($event)"
>
    @guest
        <p class="text-xs text-secondary">{{ __('Sign in to track your streak and stats.') }}</p>
    @endguest

    @if($game)
        <div class="flex h-10 items-center justify-center">
            @if($game->revealedHint())
                <p class="text-sm text-secondary text-center max-w-md line-clamp-2">{{ __('Hint: :text', ['text' => $game->revealedHint()]) }}</p>
            @endif
        </div>

        <div class="flex h-32 items-center justify-center">
            @if($game->revealedSubjectImageUrl())
                <x-picture class="shrink-0 rounded-lg overflow-hidden" :border="true">
                    <img
                        class="h-32 w-auto"
                        src="{{ $game->revealedSubjectImageUrl() }}"
                        alt=""
                    >
                </x-picture>
            @endif
        </div>

        <x-kotodama.board :game="$game" />

        <x-kotodama.keyboard :game="$game" />

        @if($flash)
            <p class="text-sm text-red-500">{{ $flash }}</p>
        @endif

        @if($game->shouldRevealAnswer())
            <x-kotodama.result :game="$game" :mode="$mode" :shareText="$shareText" />
        @endif
    @else
        <p class="text-sm text-secondary">{{ $flash ?? __('No puzzle is available today.') }}</p>
    @endif
</section>
