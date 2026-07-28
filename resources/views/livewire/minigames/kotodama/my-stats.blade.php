<main>
    <x-slot:title>
        {{ __('My Kotodama Stats') }}
    </x-slot:title>

    <x-slot:appArgument>
        kotodama/me/stats
    </x-slot:appArgument>

    <x-back-link :url="route('kotodama.daily')" :label="__('Kotodama')" :title="__('My Kotodama Stats')" />

    <div class="pb-10">

        <section class="pt-4 pl-4 pr-4 xl:safe-area-inset-scroll">
            @if(!$stats)
                <p class="text-secondary">{{ __('Sign in to track your streak and stats.') }}</p>
            @else
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 mb-6">
                    <div class="rounded border border-primary bg-secondary p-3 text-center">
                        <div class="text-2xl font-bold text-primary">{{ $stats->games_played }}</div>
                        <div class="text-xs text-secondary">{{ __('Games played') }}</div>
                    </div>

                    <div class="rounded border border-primary bg-secondary p-3 text-center">
                        <div class="text-2xl font-bold text-primary">{{ $winRate }}%</div>
                        <div class="text-xs text-secondary">{{ __('Win rate') }}</div>
                    </div>

                    <div class="rounded border border-primary bg-secondary p-3 text-center">
                        <div class="text-2xl font-bold text-primary">{{ $stats->current_streak }}</div>
                        <div class="text-xs text-secondary">{{ __('Current streak') }}</div>
                    </div>

                    <div class="rounded border border-primary bg-secondary p-3 text-center">
                        <div class="text-2xl font-bold text-primary">{{ $stats->max_streak }}</div>
                        <div class="text-xs text-secondary">{{ __('Max streak') }}</div>
                    </div>
                </div>

                <h2 class="text-lg font-semibold mb-3 text-primary">{{ __('Guess distribution') }}</h2>

                <div class="flex flex-col gap-2">
                    @foreach($distribution as $bar)
                        <div class="flex items-center gap-2">
                            <span class="w-4 text-sm font-semibold text-primary">{{ $bar->bucket }}</span>

                            <div class="flex-1 bg-secondary rounded h-5 relative">
                                <div class="h-full rounded bg-tint" style="width: {{ $bar->percent }}%;"></div>
                                <span class="absolute inset-0 flex items-center justify-end pr-2 text-xs font-semibold btn-text-tinted">{{ $bar->count }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <p class="mt-4 text-sm text-secondary">{{ __('Avg guesses') }}: {{ $averageGuesses }}</p>
            @endif
        </section>
    </div>
</main>
