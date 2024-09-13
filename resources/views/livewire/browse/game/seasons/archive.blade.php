<main>
    <x-slot:title>
        {{ __('Seasonal Archive') }} | {{ __('Games') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Browse the archive of game seasons. Join the Kurozora community and create your anime, manga and game list. Discover songs, episodes and read reviews and news!') }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Seasonal Archive') }} | {{ __('Games') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Browse the archive of game seasons. Join the Kurozora community and create your anime, manga and game list. Discover songs, episodes and read reviews and news!') }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('games.seasons.archive') }}">
    </x-slot:meta>

    <div class="max-w-7xl mx-auto pl-4 pr-4 py-6 sm:px-6">
        <h1 class="text-2xl font-bold">{{ __('Seasonal Games Archive') }}</h1>

        <section>
            <div id="mediaTypeHeader" class="pt-4 pb-5 bg-white z-10">
                <x-season-pagination :type="App\Models\Game::class" />
            </div>

            <table class="table-fixed w-full text-xs text-center border-2 sm:text-base">
                <thead id="tableHeader" class="bg-gray-100 font-bold border-2">
                <tr>
                    <th class="pt-2 pr-2 pb-2 pl-2 sm:p-4 border-b-2">
                        <p>
                            {{ __('Year') }}
                        </p>
                    </th>
                    <th class="pt-2 pr-2 pb-2 pl-2 sm:p-4 border-b-2">
                        <div class="flex justify-center h-4 sm:h-full">
                            {{ \App\Enums\SeasonOfYear::Winter()->symbol() }}
                        </div>
                    </th>
                    <th class="pt-2 pr-2 pb-2 pl-2 sm:p-4 border-b-2">
                        <div class="flex justify-center h-4 sm:h-full">
                            {{ \App\Enums\SeasonOfYear::Spring()->symbol() }}
                        </div>
                    </th>
                    <th class="pt-2 pr-2 pb-2 pl-2 sm:p-4 border-b-2">
                        <div class="flex justify-center h-4 sm:h-full">
                            {{ \App\Enums\SeasonOfYear::Summer()->symbol() }}
                        </div>
                    </th>
                    <th class="pt-2 pr-2 pb-2 pl-2 sm:p-4 border-b-2">
                        <div class="flex justify-center h-4 sm:h-full">
                            {{ \App\Enums\SeasonOfYear::Fall()->symbol() }}
                        </div>
                    </th>
                </tr>
                </thead>
                <tbody>
                @foreach (range(now()->year + 2, 1917) as $year)
                    <tr class="{{ $year === now()->year ? 'bg-orange-50' : '' }}">
                        <td>{{ $year }}</td>

                        @foreach (\App\Enums\SeasonOfYear::asSelectArray() as $seasonOfYear)
                            <td class="pt-2 pr-2 pb-2 pl-2 sm:p-4 {{ $year === now()->year && $seasonOfYear === season_of_year()->key ? 'font-semibold' : '' }}">
                                <x-simple-link href="{{ route('games.seasons.year.season', [$year, $seasonOfYear]) }}">{{ $seasonOfYear }}</x-simple-link>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table>
        </section>
    </div>

    <script>
        // Get the header
        const mediaTypeHeader = document.getElementById('mediaTypeHeader')
        const tableHeader = document.getElementById('tableHeader')

        // Get the offset position of the navbar
        const sticky = mediaTypeHeader.offsetTop

        // When the user scrolls the page, execute stickyHeader
        window.onscroll = function() { stickyHeader() }

        // Add the sticky class to the headers when you reach its scroll position. Remove "sticky" when you leave the scroll position
        function stickyHeader() {
            if (window.scrollY > sticky) {
                mediaTypeHeader.classList.add('sticky', 'top-0', 'border-b-2')
                tableHeader.classList.add('sticky')
                tableHeader.setAttribute('style', 'top:' + mediaTypeHeader.offsetHeight + 'px;')
            } else {
                mediaTypeHeader.classList.remove('sticky', 'top-0', 'border-b-2')
                tableHeader.classList.remove('sticky')
                tableHeader.removeAttribute('style')
            }
        }
    </script>
</main>
