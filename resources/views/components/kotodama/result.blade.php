@props(['game', 'mode', 'shareText'])

<div
    class="mt-6 w-full max-w-md rounded-xl border border-primary bg-secondary p-5"
    role="dialog"
    aria-live="polite"
>
    <h2 class="text-xl font-bold text-center mb-2 text-primary">
        @if($game->status?->is(\App\Enums\Minigames\Kotodama\GameStatus::Won))
            {{ __('Solved in :guesses/:max!', ['guesses' => $game->guess_count, 'max' => \App\Models\Minigames\Kotodama\Game::MAX_GUESSES]) }}
        @else
            {{ __('Out of guesses. The answer was ":answer".', ['answer' => strtoupper($game->word->answer)]) }}
        @endif
    </h2>

    @if($game->word->getHint())
        <p class="text-sm text-secondary text-center mb-3">{{ __('Hint: :text', ['text' => $game->word->getHint()]) }}</p>
    @endif

    @if($game->word->getHintImageUrl())
        <div class="flex justify-center my-3">
            <x-picture class="shrink-0 rounded-lg overflow-hidden" :border="true">
                <img
                    class="h-48 w-auto"
                    src="{{ $game->word->getHintImageUrl() }}"
                    title="{{ $game->word->getSubjectTitle() }}"
                    alt="{{ $game->word->getSubjectTitle() }}"
                >
            </x-picture>
        </div>
    @endif

    @if($shareText)
        <pre class="whitespace-pre-wrap text-center font-mono text-sm leading-none bg-primary text-primary rounded p-2 select-all">{{ $shareText }}</pre>

        <div class="flex justify-center mt-3 gap-2 flex-wrap" x-data="{ copied: false }">
            <x-button
                type="button"
                x-on:click="navigator.clipboard.writeText(@js($shareText)).then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
            >
                <span x-show="!copied">{{ __('Copy to clipboard') }}</span>
                <span x-show="copied">{{ __('Copied!') }}</span>
            </x-button>

            @if($mode->is(\App\Enums\Minigames\Kotodama\GameMode::Unlimited))
                <x-outlined-button wire:click="next">
                    {{ __('Play Unlimited') }}
                </x-outlined-button>
            @else
                <x-outlined-link-button :href="route('kotodama.unlimited')" wire:navigate>
                    {{ __('Play Unlimited') }}
                </x-outlined-link-button>
            @endif
        </div>
    @endif

    @if($game->word->getSubjectUrl())
        <div class="text-center mt-3">
            <x-simple-link :href="$game->word->getSubjectUrl()" wire:navigate>{{ __('Learn more →') }}</x-simple-link>
        </div>
    @endif
</div>
