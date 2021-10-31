<div
    x-data="{
        searchQuery: @entangle('searchQuery'),
        resetSearchQuery() {
            this.searchQuery = '';
        },
    }"
>
    <div class="absolute top-0 right-0 left-0 mx-auto max-w-full z-[300] sm:max-w-2xl"
         x-cloak=""
         x-show="isSearchEnabled"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-[400ms]"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
    >
        <div class="h-[64px]">
            <span
                class="absolute h-full w-full"
                x-show="isSearchEnabled"
                x-transition:enter="ease duration-[500ms] delay-300 transform"
                x-transition:enter-start="opacity-0 translate-x-8"
                x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
            >
                {{-- Search icon --}}
                <button class="absolute left-0 pl-4 h-full text-gray-500 sm:pl-3">
                    @svg('magnifyingglass', 'fill-current', ['width' => '16'])
                </button>

                {{-- Search field --}}
                <input class="absolute top-0 left-0 px-10 h-full w-full border-0 bg-transparent text-black focus:ring-0" type="text" placeholder="{{ __('I’m searching for…') }}" wire:model.debounce.500ms="searchQuery" />
            </span>

            {{-- Close button --}}
            <button
                class="absolute right-0 pr-4 h-full text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 sm:pr-2"
                x-show="isSearchEnabled"
                x-on:click="isSearchEnabled = false; resetSearchQuery();"
                x-transition:enter="ease duration-[400ms] delay-[325ms] transform"
                x-transition:enter-start="opacity-0 translate-x-1"
                x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
            >
                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Quick Links --}}
        <div class="absolute right-0 left-0 mx-auto p-4 max-w-7xl bg-white rounded-b-2xl sm:px-10">
            @if(!empty($searchResults))
                @foreach($searchResults as $key => $query)
                    @switch($key)
                        @case('anime')
                        @foreach($query as $anime)
                            <x-lockups.search-anime-lockup :anime="$anime" wire:key="{{ uniqid(md5($anime->title), true) }}" />

                            <hr class="my-4" />
                        @endforeach
                        @break
                        @case('user')
                        @foreach($query as $user)
                            <x-dropdown-link href="{{ route('profile.details', $user) }}">
                                {{ $user->username }}
                            </x-dropdown-link>
                        @endforeach
                        @break
                    @endswitch
                @endforeach
            @endif

            @if(!empty($quickLinks))
                <p
                    class="mt-2 text-md text-gray-500 font-semibold uppercase"
                    x-show="isSearchEnabled"
                    x-transition:enter="ease duration-[400ms] transform"
                    x-transition:enter-start="opacity-0 translate-x-8"
                    x-transition:enter-end="opacity-100 translate-x-0"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                >{{ __('Quick Links') }}</p>

                <ul class="space-y-4">
                    @foreach($quickLinks as $key => $quickLink)
                        <li
                            x-show="isSearchEnabled"
                            x-bind:style="isSearchEnabled ? 'transition-duration: {{ $key * 50 + 400 }}ms;' : ''"
                            x-transition:enter="ease duration-100 transform"
                            x-transition:enter-start="opacity-0 translate-x-8"
                            x-transition:enter-end="opacity-100 translate-x-0"
                            x-transition:leave="ease-in duration-200"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                        >
                            <x-footer-link class="inline-block w-full" href="{{ $quickLink['link'] }}">{{ $quickLink['title'] }}</x-footer-link>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    {{-- Overlay --}}
    <div
        class="fixed inset-0 transform transition-all z-[299]"
        x-show="isSearchEnabled"
        x-on:click="isSearchEnabled = false; resetSearchQuery();"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div class="absolute inset-0 bg-black opacity-75"></div>
    </div>
</div>
