@props(['game'])

<div
    class="kotodama-board flex flex-col gap-1 items-center"
    x-bind:class="{ 'kotodama-differentiate': $store.kotodamaAccessibility.enabled }"
    aria-label="{{ __('Kotodama board') }}"
>
    @foreach($game->boardRows() as $row)
        <div class="flex gap-1">
            @foreach($row['cells'] as $column => $cell)
                <div @class([
                    'relative flex items-center justify-center w-12 h-12 border-2 rounded text-lg font-bold uppercase',
                    'kotodama-tile-hit' => $cell['feedback'] === \App\Enums\Minigames\Kotodama\Feedback::Hit,
                    'kotodama-tile-present' => $cell['feedback'] === \App\Enums\Minigames\Kotodama\Feedback::Present,
                    'kotodama-tile-miss' => $cell['feedback'] === \App\Enums\Minigames\Kotodama\Feedback::Miss,
                    'bg-primary border-primary text-primary' => $cell['feedback'] === null,
                ])>
                    @if($cell['letter'])
                        {{ $cell['letter'] }}
                    @elseif($row['isActive'])
                        <span x-text="(currentGuess[{{ $column }}] || '').toUpperCase()"></span>
                    @endif
                </div>
            @endforeach
        </div>
    @endforeach
</div>
