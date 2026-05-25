<main>
    <x-slot:title>
        {{ $this->category->description }} | {{ __('Parents Guide') }} | {!! $this->parent->title !!}
    </x-slot:title>

    <x-slot:description>
        {{ __(':x parental guide on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $this->parent->title, 'y' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <link rel="canonical" href="{{ $this->canonicalUrl }}">
    </x-slot:meta>

    <x-slot:appArgument>
        {{ $this->appArgumentSegment }}/{{ $this->parent->id }}/parentalguide
    </x-slot:appArgument>

    <div class="pb-6">
        <x-back-link
            :url="$this->parentalGuideUrl"
            :label="__(':x’s Parents Guide', ['x' => $this->parent->title])"
            :title="$this->category->description"
        />

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
