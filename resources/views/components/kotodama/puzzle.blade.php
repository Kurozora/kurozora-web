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
        @if($game->word?->getSubjectKindName())
            <p class="text-xs font-semibold text-secondary">{{ $game->word->getSubjectKindName() }}</p>
        @endif

        <x-kotodama.board :game="$game" />

        @if(!$game->isFinished() || $flash)
            <div class="flex h-28 w-full max-w-2xl items-center justify-center gap-3">
                @if($flash)
                    <p class="text-sm text-red-500 text-center" role="alert">{{ $flash }}</p>
                @elseif($game->revealedHint())
                    @if($game->revealedSubjectImageUrl())
                        <x-kotodama.subject-image :word="$game->word" />
                    @endif

                    <p class="min-w-0 text-sm text-secondary whitespace-pre-line">{{ collect([$game->revealedHint(), $game->revealedSecondaryHint()])->filter()->implode("\n") }}</p>
                @endif
            </div>
        @endif

        @if (!$game->isFinished())
            <x-kotodama.keyboard :game="$game" />
        @endif

        @if($game->shouldRevealAnswer())
            <x-kotodama.result :game="$game" :mode="$mode" :shareText="$shareText" />
        @endif
    @else
        <p class="text-sm text-secondary">{{ $flash ?? __('No puzzle is available today.') }}</p>
    @endif
</section>
