<x-base-layout>
    <x-slot:title>
        {{ $category->description }} | {{ __('Parents Guide') }} | {!! $parent->title !!}
    </x-slot:title>

    <x-slot:description>
        {{ __(':x parental guide on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $parent->title, 'y' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <link rel="canonical" href="{{ $canonicalUrl }}">
    </x-slot:meta>

    <x-slot:appArgument>
        {{ $appArgumentSegment }}/{{ $parent->id }}/parentalguide
    </x-slot:appArgument>

    <main>
        <div class="pb-6">
            <x-back-link
                :url="$parentalGuideUrl"
                :label="__(':x’s Parents Guide', ['x' => $parent->title])"
                :title="$category->description"
            />

            <section class="mb-4 xl:safe-area-inset" data-paginated="parental-guide-entries" data-paginated-refresh-on="parental-guide-updated">
                <div class="flex flex-col gap-4 pl-4 pr-4">
                    @if ($entries->isEmpty())
                        <p class="text-secondary">{{ __('No entries to show.') }}</p>
                    @else
                        <div class="flex flex-col gap-4">
                            @foreach ($entries as $entry)
                                <x-lockups.parental-guide-entry-lockup :entry="$entry" />
                            @endforeach
                        </div>

                        <div>
                            {{ $entries->links() }}
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </main>

    <x-parental-guide.modals />

    @auth
        <livewire:components.parental-guide-box :model-id="$parent->id" :model-type="$parent->getMorphClass()" />
    @endauth
</x-base-layout>
