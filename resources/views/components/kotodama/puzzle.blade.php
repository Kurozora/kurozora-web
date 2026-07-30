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
        <x-kotodama.board :game="$game" />

        <div class="flex h-10 items-center justify-center">
            @if($flash)
                <p class="text-sm text-red-500 text-center max-w-md line-clamp-2">{{ $flash }}</p>
            @endif
        </div>

        @if (!$game->isFinished())
            <x-kotodama.keyboard :game="$game" />

            <div class="flex h-10 items-center justify-center">
                @if ($game->revealedHint())
                    <p class="text-sm text-secondary text-center max-w-md line-clamp-2">{{ __('Hint: :text', ['text' => $game->revealedHint()]) }}</p>
                @endif
            </div>

            <div class="flex h-40 items-center justify-center">
                @if ($game->revealedSubjectImageUrl())
                    <x-kotodama.subject-image :word="$game->word" />
                @endif
            </div>
        @endif

        @if($game->shouldRevealAnswer())
            <x-kotodama.result :game="$game" :mode="$mode" :shareText="$shareText" />
        @endif
    @else
        <p class="text-sm text-secondary">{{ $flash ?? __('No puzzle is available today.') }}</p>
    @endif
</section>
