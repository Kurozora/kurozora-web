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
        <section class="pl-4 pr-4 xl:safe-area-inset-scroll">
            <h1 class="text-2xl font-bold text-primary">{{ $title }}</h1>
        </section>

        <x-kotodama.nav />

        <div class="pt-4">
            <x-kotodama.puzzle
                :game="$game"
                :mode="$mode"
                :shareText="$this->shareText()"
                :flash="$flash"
            />
        </div>

        @auth
            <x-kotodama.streak :stats="$stats" :winRate="$winRate" :recentResults="$recentResults" />
        @endauth

        @if($game)
            <x-kotodama.leaderboard-peek :topEntries="$topEntries" />
        @endif
    </div>
</main>
