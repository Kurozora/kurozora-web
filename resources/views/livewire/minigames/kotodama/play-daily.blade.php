<main>
    <x-slot:title>
        {{ __('Kotodama') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Guess the hidden anime word in six tries.') }}
    </x-slot:description>

    <x-slot:appArgument>
        kotodama
    </x-slot:appArgument>

    <div class="pt-4 pb-10">
        <section class="flex items-center justify-between pl-4 pr-4 xl:safe-area-inset-scroll">
            <h1 class="text-2xl font-bold text-primary">{{ $title }}</h1>

            <x-square-button wire:click="$toggle('showHelp')" aria-label="{{ __('How to play') }}">
                @svg('questionmark', 'fill-current', ['width' => 20])
            </x-square-button>
        </section>

        <x-dialog-modal maxWidth="md" model="showHelp">
            <x-slot:title>
                {{ __('How to Play') }}
            </x-slot:title>

            <x-slot:description>
                {{ __('Guess the hidden word in six tries. Every answer is five letters long and pulled straight from the Kurozora catalog: anime, manga, game and song titles, plus characters, people and studios.') }}
            </x-slot:description>

            <x-slot:content>
                <div class="flex flex-col gap-4 pt-4 pb-4 pl-4 pr-4">
                    <p class="text-primary">{{ __('Type a guess and press the return key. The tiles change color after every guess to show how close you are.') }}</p>

                    <div>
                        <h2 class="text-lg font-semibold text-primary pb-3">{{ __('What the colors mean') }}</h2>

                        <div
                            class="kotodama-board flex flex-col gap-3"
                            x-bind:class="{ 'kotodama-differentiate': $store.kotodamaAccessibility.enabled }"
                        >
                            <div class="flex items-center gap-3">
                                <x-kotodama.tile letter="K" state="hit" />

                                <p class="text-secondary">{{ __('The letter is in the word, right where you put it.') }}</p>
                            </div>

                            <div class="flex items-center gap-3">
                                <x-kotodama.tile letter="O" state="present" />

                                <p class="text-secondary">{{ __('The letter is in the word, but somewhere else.') }}</p>
                            </div>

                            <div class="flex items-center gap-3">
                                <x-kotodama.tile letter="T" state="miss" />

                                <p class="text-secondary">{{ __('The letter is not in the word at all.') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-1">
                        <x-toggle-button
                            x-on:click="$store.kotodamaAccessibility.toggle()"
                            x-bind:aria-pressed="$store.kotodamaAccessibility.enabled ? 'true' : 'false'"
                            x-bind:class="{ 'bg-tint btn-text-tinted border-transparent': $store.kotodamaAccessibility.enabled, 'bg-primary text-tint border-tint': !$store.kotodamaAccessibility.enabled }"
                        >
                            {{ __('Differentiate without color') }}
                        </x-toggle-button>

                        <p class="text-sm text-secondary">{{ __('Marks correct letters with a filled circle and misplaced letters with a hollow circle.') }}</p>
                    </div>

                    <p class="text-primary">{{ __('Need a nudge? A hint shows up after your third guess. From the fifth guess on you also get a picture.') }}</p>

                    <p class="text-primary">{{ __('A new puzzle drops every day at midnight. Solve it to keep your streak going, and replay older puzzles from the archive whenever you like.') }}</p>
                </div>
            </x-slot:content>

            <x-slot:footer>
                <x-button wire:click="$toggle('showHelp')">{{ __('OK') }}</x-button>
            </x-slot:footer>
        </x-dialog-modal>

        <x-kotodama.nav />

        @if($game && $game->isFinished() && $nextPuzzleAt)
            <div class="flex h-10 items-center justify-center">
                <p
                    class="text-sm text-primary text-center"
                    data-template="{{ __('Next Kotodama in :time') }}"
                    x-data="kotodamaCountdown({ unlockAt: {{ $nextPuzzleAt }} })"
                    x-init="start()"
                    x-text="text"
                ></p>
            </div>
        @endif

        <div class="pt-4">
            <x-kotodama.puzzle
                :game="$game"
                :mode="$mode"
                :shareText="$this->shareText()"
                :flash="$flash"
            />
        </div>

        @auth
            <x-kotodama.streak :stats="$stats" :winRate="$winRate" />
        @endauth

        @if($game)
            <x-kotodama.leaderboard-peek :topEntries="$topEntries" />
        @endif
    </div>
</main>
