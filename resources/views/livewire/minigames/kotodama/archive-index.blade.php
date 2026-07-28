<main>
    <x-slot:title>
        {{ __('Kotodama Archive') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Play any past Kotodama puzzle.') }}
    </x-slot:description>

    <x-slot:appArgument>
        kotodama/archive
    </x-slot:appArgument>

    <x-back-link :url="route('kotodama.daily')" :label="__('Kotodama')" :title="__('Kotodama Archive')" />

    <div class="pb-10">
        @if($entries->isEmpty())
            <x-kotodama.empty
                :title="__('No Past Puzzles Yet')"
                :description="__('Once today\'s puzzle rolls over it lands here, ready to play any time.')"
            >
                <x-slot:action>
                    <x-outlined-link-button :href="route('kotodama.daily')" wire:navigate>
                        {{ __('Play Today\'s Kotodama') }}
                    </x-outlined-link-button>
                </x-slot:action>
            </x-kotodama.empty>
        @else
            <section class="pl-4 pr-4 xl:safe-area-inset-scroll">
                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-2">
                    @foreach($entries as $entry)
                        <x-simple-link :href="route('kotodama.archive.play', ['date' => $entry->date])" wire:navigate>
                            <span class="flex flex-col items-center justify-center rounded-lg border border-primary bg-secondary p-3 hover:bg-tertiary transition">
                                <span class="text-xs text-secondary">#{{ $entry->puzzleNumber }}</span>
                                <span class="text-sm font-semibold text-primary">{{ $entry->date }}</span>

                                @if($entry->solved)
                                    <span class="text-xs text-tint">{{ __('Solved') }}</span>
                                @endif
                            </span>
                        </x-simple-link>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</main>
