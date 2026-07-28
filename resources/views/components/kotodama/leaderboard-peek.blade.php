@props(['topEntries'])

<section class="pt-10 pl-4 pr-4 xl:safe-area-inset-scroll">
    <div class="flex items-center justify-between pb-3">
        <h2 class="text-lg font-semibold text-primary">{{ __('Today\'s Fastest') }}</h2>

        <x-simple-link :href="route('kotodama.leaderboards')" wire:navigate>
            {{ __('See All') }}
        </x-simple-link>
    </div>

    @if($topEntries->isEmpty())
        <p class="text-sm text-secondary">{{ __('Nobody has solved today\'s puzzle yet.') }}</p>
    @else
        <ol class="flex flex-col gap-2">
            @foreach($topEntries as $index => $entry)
                <li class="flex items-center justify-between rounded-lg border border-primary bg-secondary pt-2 pb-2 pl-4 pr-4">
                    <span class="font-semibold text-primary">{{ $index + 1 }} {{ $entry->user?->username }}</span>

                    <span class="text-sm text-secondary">
                        {{ $entry->guess_count }}/{{ \App\Models\Minigames\Kotodama\Game::MAX_GUESSES }}
                        ·
                        {{ __(':seconds s', ['seconds' => number_format(($entry->duration_ms ?? 0) / 1000, 1)]) }}
                    </span>
                </li>
            @endforeach
        </ol>
    @endif
</section>
