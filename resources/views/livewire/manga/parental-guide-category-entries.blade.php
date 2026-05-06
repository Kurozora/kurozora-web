<main>
    <x-slot:title>
        {{ $this->category->description }} | {{ __('Parents Guide') }} | {!! $manga->title !!}
    </x-slot:title>

    <x-slot:description>
        {{ __(':x parental guide on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $manga->title, 'y' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <link rel="canonical" href="{{ route('manga.parentalguide.category', ['manga' => $manga, 'category' => $this->category->urlSlug()]) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        manga/{{ $manga->id }}/parentalguide
    </x-slot:appArgument>

    <div class="pb-6">
        <section class="sticky top-0 mb-4 pt-4 pb-4 backdrop-blur bg-blur z-10 xl:safe-area-inset">
            <div class="flex flex-col gap-1 pl-4 pr-4">
                <a href="{{ route('manga.parentalguide', $manga) }}" class="text-secondary text-sm">
                    ← {{ __(':x’s Parents Guide', ['x' => $manga->title]) }}
                </a>

                <h1 class="text-2xl font-bold">{{ $this->category->description }}</h1>
            </div>
        </section>

        <section class="mb-4 xl:safe-area-inset" wire:init="loadPage">
            <div class="flex flex-col gap-4 pl-4 pr-4">
                @if (!$readyToLoad)
                    <x-skeletons.parental-guide-entry-lockup />
                    <x-skeletons.parental-guide-entry-lockup />
                    <x-skeletons.parental-guide-entry-lockup />
                    <x-skeletons.parental-guide-entry-lockup />
                    <x-skeletons.parental-guide-entry-lockup />
                @elseif ($this->entries->isEmpty())
                    <p class="text-secondary">{{ __('No entries to show.') }}</p>
                @else
                    <div class="flex flex-col gap-4">
                        @foreach ($this->entries as $entry)
                            @include('livewire.components.parental-guide.entry-lockup', [
                                'entry' => $entry,
                            ])
                        @endforeach
                    </div>

                    <div>
                        {{ $this->entries->links() }}
                    </div>
                @endif
            </div>
        </section>
    </div>

    @include('livewire.components.parental-guide.submit-form')
    @include('livewire.components.parental-guide.report-form')

</main>
