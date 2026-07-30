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
        <div class="flex flex-col gap-2">
            @foreach($topEntries as $index => $entry)
                <div class="relative flex items-center justify-between rounded-lg border border-primary bg-secondary pt-2 pb-2 pl-4 pr-4">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="shrink-0 font-bold text-primary">#{{ $index + 1 }}</span>

                        <x-profile-image-view class="w-10 h-10 shrink-0" :user="$entry->user" />

                        <span class="font-semibold text-primary truncate">{{ $entry->user?->username }}</span>
                    </div>

                    <span class="shrink-0 text-sm text-secondary">
                        {{ $entry->guess_count }}/{{ \App\Models\Minigames\Kotodama\Game::MAX_GUESSES }}
                        ·
                        {{ __(':seconds s', ['seconds' => number_format(($entry->duration_ms ?? 0) / 1000, 1)]) }}
                    </span>

                    @if($entry->user)
                        <a class="absolute top-0 left-0 h-full w-full" href="{{ route('profile.details', $entry->user) }}" wire:navigate></a>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</section>
