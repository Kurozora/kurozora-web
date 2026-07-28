<main>
    <x-slot:title>
        {{ __('Kotodama Leaderboards') }}
    </x-slot:title>

    <x-slot:appArgument>
        kotodama/leaderboards
    </x-slot:appArgument>

    <x-back-link :url="route('kotodama.daily')" :label="__('Kotodama')" :title="__('Kotodama Leaderboards')" />

    <div class="pb-10">
        <section class="flex gap-2 pl-4 pr-4 xl:safe-area-inset-scroll">
            @if($tab === 'daily')
                <x-button type="button">{{ __('Daily') }}</x-button>

                <x-outlined-button wire:click="$set('tab', 'streak')">
                    {{ __('Streak') }}
                </x-outlined-button>
            @else
                <x-outlined-button wire:click="$set('tab', 'daily')">
                    {{ __('Daily') }}
                </x-outlined-button>

                <x-button type="button">{{ __('Streak') }}</x-button>
            @endif
        </section>

        @if($tab === 'daily')
            @if($dailyEntries->isEmpty())
                <x-kotodama.empty
                    :title="__('No Solves Yet')"
                    :description="__('Be the first to solve today\'s puzzle.')"
                >
                    <x-slot:action>
                        <x-outlined-link-button :href="route('kotodama.daily')" wire:navigate>
                            {{ __('Play Today\'s Kotodama') }}
                        </x-outlined-link-button>
                    </x-slot:action>
                </x-kotodama.empty>
            @else
                <section class="pt-6 pl-4 pr-4 xl:safe-area-inset-scroll">
                    <ol class="flex flex-col gap-2">
                        @foreach($dailyEntries as $index => $entry)
                            <li class="flex items-center justify-between rounded-lg border border-primary bg-secondary pt-2 pb-2 pl-4 pr-4">
                                <span class="font-semibold text-primary">#{{ $index + 1 }} {{ $entry->user?->username }}</span>

                                <span class="text-sm text-secondary">
                                    {{ __(':count guesses', ['count' => $entry->guess_count]) }}
                                    ·
                                    {{ __(':seconds s', ['seconds' => number_format(($entry->duration_ms ?? 0) / 1000, 1)]) }}
                                </span>
                            </li>
                        @endforeach
                    </ol>
                </section>
            @endif
        @else
            @if($streakEntries->isEmpty())
                <x-kotodama.empty
                    :title="__('No Streaks Yet')"
                    :description="__('Streaks build up as players keep solving the daily puzzle.')"
                >
                    <x-slot:action>
                        <x-outlined-link-button :href="route('kotodama.daily')" wire:navigate>
                            {{ __('Play Today\'s Kotodama') }}
                        </x-outlined-link-button>
                    </x-slot:action>
                </x-kotodama.empty>
            @else
                <section class="pt-6 pl-4 pr-4 xl:safe-area-inset-scroll">
                    <ol class="flex flex-col gap-2">
                        @foreach($streakEntries as $index => $entry)
                            <li class="flex items-center justify-between rounded-lg border border-primary bg-secondary pt-2 pb-2 pl-4 pr-4">
                                <span class="font-semibold text-primary">#{{ $index + 1 }} {{ $entry->user?->username }}</span>

                                <span class="text-sm text-secondary">{{ __('Best :best · Current :current', ['best' => $entry->max_streak, 'current' => $entry->current_streak]) }}</span>
                            </li>
                        @endforeach
                    </ol>
                </section>
            @endif
        @endif
    </div>
</main>
