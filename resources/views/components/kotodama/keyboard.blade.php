@props(['game'])

<div class="flex flex-col items-center gap-1 mt-4 w-full max-w-lg">
    @foreach(['qwertyuiop', 'asdfghjkl', 'zxcvbnm'] as $row)
        <div class="flex gap-1 justify-center w-full">
            @foreach(str_split($row) as $key)
                <button
                    type="button"
                    @class([
                        'flex-1 max-w-10 pt-3 pb-3 rounded font-semibold uppercase text-sm transition disabled:cursor-default',
                        'kotodama-tile-hit' => ($game->keyboardStates()[$key] ?? null) === \App\Enums\Minigames\Kotodama\Feedback::Hit,
                        'kotodama-tile-present' => ($game->keyboardStates()[$key] ?? null) === \App\Enums\Minigames\Kotodama\Feedback::Present,
                        'kotodama-tile-miss' => ($game->keyboardStates()[$key] ?? null) === \App\Enums\Minigames\Kotodama\Feedback::Miss,
                        'bg-secondary text-primary hover:bg-tertiary' => !isset($game->keyboardStates()[$key]),
                    ])
                    x-on:click="pressKey('{{ $key }}')"
                    @disabled($game->isFinished())
                >
                    {{ $key }}
                </button>
            @endforeach
        </div>
    @endforeach

    <div class="flex gap-1 justify-center w-full mt-1">
        <button
            type="button"
            class="pl-4 pr-4 pt-3 pb-3 rounded font-semibold text-sm bg-secondary text-primary hover:bg-tertiary transition disabled:cursor-default"
            x-on:click="backspaceKey()"
            @disabled($game->isFinished())
        >
            ⌫
        </button>

        <button
            type="button"
            class="pl-4 pr-4 pt-3 pb-3 rounded font-semibold text-sm bg-tint btn-text-tinted hover:bg-tint-800 transition disabled:cursor-default"
            x-on:click="submitGuess()"
            x-bind:disabled="currentGuess.length !== {{ \App\Models\Minigames\Kotodama\Word::LENGTH }} || {{ $game->isFinished() ? 'true' : 'false' }}"
        >
            ↵
        </button>
    </div>
</div>
