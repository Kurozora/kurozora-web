<main>
    <x-slot:title>
        {{ __(':x Schedule', ['x' => class_basename($class)]) }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Explore the latest :x schedule on :y. Stay updated with upcoming episode broadcasts and countdowns.', ['x' => strtolower(class_basename($class)), 'y' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __(':x Schedule', ['x' => class_basename($class)]) }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Explore the latest :x schedule on :y. Stay updated with upcoming episode broadcasts and countdowns.', ['x' => strtolower(class_basename($class)), 'y' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('schedule') }}">
    </x-slot:meta>

    <div
        class="py-6"
        x-data="{
            selectedDate: null
        }"
    >
        <section class="flex gap-1 pl-4 pr-4">
            <div class="flex flex-wrap items-center w-full">
                <h1 class="text-2xl font-bold">{{ __(':x Schedule', ['x' => class_basename($class)]) }}</h1>
            </div>

            <div class="flex flex-wrap justify-end items-center w-full">
            </div>
        </section>

        <section id="dateHeader" class="bg-primary pt-4 pb-4 z-10">
            <x-season-pagination :type="$class" />

            <x-hr class="mt-4 mb-4 ml-4 mr-4" />

            <div class="flex gap-2 pl-4 pr-4 overflow-x-scroll no-scrollbar">
                <template x-if="selectedDate === null">
                    <x-button>{{ __('All') }}</x-button>
                </template>

                <template x-if="selectedDate !== null">
                    <x-outlined-button x-on:click="selectedDate = null">{{ __('All') }}</x-outlined-button>
                </template>

                @foreach ($this->dates as $date)
                    <template x-if="selectedDate === '{{ $date->toDateString() }}'">
                        <x-button>{{ $date->format('l') }}</x-button>
                    </template>

                    <template x-if="selectedDate !== '{{ $date->toDateString() }}'">
                        <x-outlined-button x-on:click="selectedDate = '{{ $date->toDateString() }}'">{{ $date->format('l') }}</x-outlined-button>
                    </template>
                @endforeach
            </div>
        </section>

        <section class="flex flex-col">
            @foreach ($this->dates as $date)
                <div
                    x-show="selectedDate === '{{ $date->toDateString() }}' || selectedDate === null"
                    class="pb-10"
                    x-bind:class="{'bg-tinted': '{{ $date->toDateString() }}' === '{{ today()->toDateString() }}' }"
                >
                    <livewire:sections.schedule :class="$class" :date="$date" />
                </div>
            @endforeach
        </section>
    </div>

    <script>
        // Get the header
        const header = document.getElementById('dateHeader')

        // Get the offset position of the navbar
        const sticky = header.offsetTop

        // When the user scrolls the page, execute stickyHeader
        window.onscroll = function() { stickyHeader() }

        // Add the sticky class to the header when you reach its scroll position. Remove "sticky" when you leave the scroll position
        function stickyHeader() {
            if (window.scrollY > sticky) {
                header.classList.add('sticky', 'top-0', 'border-b', 'border-primary')
            } else {
                header.classList.remove('sticky', 'top-0', 'border-b', 'border-primary')
            }
        }
    </script>
</main>
