<x-base-layout>
    <x-slot:title>
        {{ $seasonOfYear->key . ' ' . $year }} | {{ $noun }}
    </x-slot:title>

    <x-slot:description>
        {{ $description }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ $seasonOfYear->key . ' ' . $year }} | {{ $noun }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ $description }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ $canonicalUrl }}">
    </x-slot:meta>

    <main>
        <div
            class="pt-4 pb-6 xl:safe-area-inset"
            x-data="{
                selectedMediaType: null
            }"
        >
            <section class="flex gap-1 pl-4 pr-4">
                <div class="flex flex-wrap items-center w-full">
                    <h1 class="text-2xl font-bold">{{ $heading }}</h1>
                </div>

                <div class="flex flex-wrap justify-end items-center w-full">
                </div>
            </section>

            <section id="mediaTypeHeader" class="bg-primary pt-4 pb-4 z-10">
                <x-season-pagination :type="$modelClass" :season-of-year="$seasonOfYear" :year="$year" />

                <x-hr class="mt-4 mb-4 ml-4 mr-4" />

                @if ($mediaTypes->count())
                    <div class="flex gap-2 pl-4 pr-4 overflow-x-scroll no-scrollbar">
                        <template x-if="selectedMediaType === null">
                            <x-button>{{ __('All') }}</x-button>
                        </template>

                        <template x-if="selectedMediaType !== null">
                            <x-outlined-button x-on:click="selectedMediaType = null">{{ __('All') }}</x-outlined-button>
                        </template>

                        @foreach ($mediaTypes as $mediaType)
                            <template x-if="selectedMediaType === '{{ $mediaType->name }}'">
                                <x-button class="whitespace-nowrap">{{ $mediaType->name }}</x-button>
                            </template>

                            <template x-if="selectedMediaType !== '{{ $mediaType->name }}'">
                                <x-outlined-button class="whitespace-nowrap" x-on:click="selectedMediaType = '{{ $mediaType->name }}'">{{ $mediaType->name }}</x-outlined-button>
                            </template>
                        @endforeach
                    </div>
                @endif
            </section>

            @if ($mediaTypes->count())
                <section class="space-y-10">
                    @foreach ($mediaTypes as $mediaType)
                        <div x-show="selectedMediaType === '{{ $mediaType->name }}' || selectedMediaType === null">
                            <x-browse.seasons-section :class="$modelClass" :media-type="$mediaType" :season-of-year="$seasonOfYear->value" :year="$year" />
                        </div>
                    @endforeach
                </section>
            @else
                <x-empty-state class="flex flex-col items-center justify-center mt-4 text-center" :image="$emptyImage" :heading="$emptyHeading" :description="$emptyDescription" />
            @endif
        </div>

        <script>
            const header = document.getElementById('mediaTypeHeader')
            const sticky = header.offsetTop

            window.onscroll = function() { stickyHeader() }

            function stickyHeader() {
                if (window.scrollY > sticky) {
                    header.classList.add('sticky', 'top-0', 'border-b', 'border-primary')
                } else {
                    header.classList.remove('sticky', 'top-0', 'border-b', 'border-primary')
                }
            }
        </script>
    </main>
</x-base-layout>
