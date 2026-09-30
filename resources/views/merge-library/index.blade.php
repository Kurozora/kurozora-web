<x-base-layout>
    <x-slot:title>
        {{ __('Merge Library') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Connect your local library with your :x Account — the largest, free online anime, manga, game & music database.', ['x' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Merge Library') }} — {{ config('app.name') }}" />
        <meta property="og:description"
              content="{{ __('Connect your local library with your :x Account — the largest, free online anime, manga, game & music database.', ['x' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('merge-library') }}">
    </x-slot:meta>

    <main>
        <header class="bg-secondary shadow">
            <div class="flex pt-4 pb-6 pl-4 pr-4">
                <h2 class="font-semibold text-xl leading-tight">
                    {{ __('Merge Library') }}
                </h2>
            </div>
        </header>

        <div class="xl:safe-area-inset">
            <div
                x-data="{
                    groupedItems: {},
                    latestCreationDate: null,
                    popupType: '',
                    popups: {{ Js::from([
                        'local' => [
                            'title' => __('Keep Local Library'),
                            'message' => __('Selecting this option will overwrite your :x Library with the data from your Local Library. Your Local Library will be erased after that.', ['x' => config('app.name')]),
                        ],
                        'kurozora' => [
                            'title' => __('Keep :x Library', ['x' => config('app.name')]),
                            'message' => __('Selecting this option will erase your Local Library while preserving your :x Library.', ['x' => config('app.name')]),
                        ],
                        'merge' => [
                            'title' => __('Merge Both Libraries'),
                            'message' => __('Selecting this option will merge your Local Library with your :x Library. Your Local Library will be erased after that.', ['x' => config('app.name')]),
                        ],
                    ]) }},
                    busy: false,
                    openPopup(type) {
                        this.popupType = type
                        this.busy = false
                        this.$dispatch('open-modal', { id: 'merge-library-confirm' })
                    },
                    closePopup() {
                        this.$dispatch('close-modal', { id: 'merge-library-confirm' })
                    },
                    keepLocalLibrary() {
                        this.busy = true
                        this.getLocalLibraryJSON().then((library) => window.Livewire.dispatch('local-library-overwrite', { library: library }))
                    },
                    keepKurozoraLibrary() {
                        this.busy = true
                        this.clearLocalLibrary(false)
                    },
                    mergeLibraries() {
                        this.busy = true
                        this.getLocalLibraryJSON().then((library) => window.Livewire.dispatch('local-library-merge', { library: library }))
                    },
                    formatUnixTimestamp(timestamp) {
                        if (timestamp === null) {
                            return ''
                        }

                        return new Date(this.latestCreationDate * 1000).toISOString().replace(/T/, ' ').replace(/\..+/, '')
                    },
                    getStatusDescription(modelType, libraryStatus) {
                        // Define a mapping of integer values to descriptions
                        const statusArrayMap = {
                            'Anime': {{ json_encode(\App\Enums\UserLibraryStatus::asAnimeSelectArray()) }},
                            'Manga': {{ json_encode(\App\Enums\UserLibraryStatus::asMangaSelectArray()) }},
                            'Game': {{ json_encode(\App\Enums\UserLibraryStatus::asGameSelectArray()) }}
                        }

                        // Status descriptions for the model type
                        const statusDescriptions = statusArrayMap[modelType] || {}

                        // Return the corresponding description for the given integer value
                        return statusDescriptions[libraryStatus] || 'Unknown'
                    },
                    getItemsGroupedByStatus() {
                        // Ensure the database is initialized
                        if (!window.libraryDB) {
                            console.error('IndexedDB not initialized.')
                            return {}
                        }

                        // Start a transaction and get the object store
                        let transaction = window.libraryDB.transaction(['libraryData'], 'readonly')
                        let objectStore = transaction.objectStore('libraryData')

                        // Open a cursor to iterate over the entries
                        let cursorRequest = objectStore.openCursor()

                        this.groupedItems = {}
                        this.latestCreationDate = null

                        // Sorted entries
                        let sortedEntries = []

                        cursorRequest.onsuccess = function (event) {
                            let cursor = event.target.result

                            if (cursor) {
                                // Collect entries for sorting
                                sortedEntries.push(cursor.value)

                                // Track the latest creationDate
                                if (this.latestCreationDate === null || cursor.value.creationDate > this.latestCreationDate) {
                                    this.latestCreationDate = cursor.value.creationDate
                                }

                                cursor.continue()
                            } else {
                                // Sort entries by libraryCategory
                                sortedEntries.sort((a, b) => a.libraryCategory.localeCompare(b.libraryCategory))

                                // Process sorted entries
                                for (let entry of sortedEntries) {
                                    let modelType = entry.libraryKind
                                    let libraryStatus = entry.libraryCategory

                                    // The description for the status value
                                    let descriptionKey = this.getStatusDescription(modelType, libraryStatus)

                                    // Count per status
                                    this.groupedItems[modelType] = this.groupedItems[modelType] || {}
                                    this.groupedItems[modelType][descriptionKey] = this.groupedItems[modelType][descriptionKey] || 0

                                    // Increment the count for the specific library status
                                    this.groupedItems[modelType][descriptionKey]++
                                }

                                if (Object.keys(this.groupedItems).length === 0) {
                                    window.Livewire.dispatch('local-library-empty')
                                }
                            }
                        }.bind(this)

                        cursorRequest.onerror = function (event) {
                            console.error('Error iterating over entries:', event.target.error)
                        }
                    },
                    clearLocalLibrary(merging) {
                        // Ensure the database is initialized
                        if (!window.libraryDB) {
                            console.error('IndexedDB not initialized.')
                            return {}
                        }

                        // Open a connection to the database
                        let request = window.indexedDB.open('library')

                        // Handle database opening success
                        request.onsuccess = function(event) {
                            let db = event.target.result

                            // Open a transaction and get the object store
                            let transaction = db.transaction(['libraryData'], 'readwrite')
                            let objectStore = transaction.objectStore('libraryData')

                            // Clear all entries from the object store
                            let clearRequest = objectStore.clear()

                            // Handle the success of clearing the object store
                            clearRequest.onsuccess = function(merging) {
                                return function(event) {
                                    console.log('Local Library cleared successfully.', merging)

                                    window.Livewire.dispatch('local-library-cleared', { merging: merging })
                                }
                            }(merging)

                            // Handle any errors during clearing
                            clearRequest.onerror = function(event) {
                                console.error('Error clearing Local Library:', event.target.error)
                            }
                        }

                        // Handle database opening errors
                        request.onerror = function(event) {
                            console.error('Error opening IndexedDB:', event.target.error)
                        }
                    },
                    getLocalLibraryJSON() {
                        // Ensure the database is initialized
                        if (!window.libraryDB) {
                            console.error('IndexedDB not initialized.');
                            return null;
                        }

                        // Open a connection to the database
                        let request = window.indexedDB.open('library');

                        // Return a promise to handle the asynchronous nature of IndexedDB
                        return new Promise((resolve, reject) => {
                            // Handle database opening success
                            request.onsuccess = function(event) {
                                let db = event.target.result;

                                // Open a transaction and get the object store
                                let transaction = db.transaction(['libraryData'], 'readonly');
                                let objectStore = transaction.objectStore('libraryData');

                                // Open a cursor to iterate over the entries
                                let cursorRequest = objectStore.openCursor();

                                let libraryData = [];

                                // Handle cursor success
                                cursorRequest.onsuccess = function(event) {
                                    let cursor = event.target.result;

                                    if (cursor) {
                                        // Collect the entry
                                        libraryData.push(cursor.value);

                                        // Move to the next entry
                                        cursor.continue();
                                    } else {
                                        // Cursor has iterated over all entries
                                        resolve(JSON.stringify(libraryData));
                                    }
                                };

                                // Handle cursor errors
                                cursorRequest.onerror = function(event) {
                                    console.error('Error iterating over entries:', event.target.error);
                                    reject('Error retrieving Local Library.');
                                };
                            };

                            // Handle database opening errors
                            request.onerror = function(event) {
                                console.error('Error opening IndexedDB:', event.target.error);
                                reject('Error opening IndexedDB.');
                            };
                        });
                    }
                }"
                class="flex flex-col justify-center w-full max-w-prose mx-auto pt-4 pb-6 pl-4 pr-4"
                x-on:clear-local-library.window="clearLocalLibrary(true)"
                x-on:user-actions-failed.window="busy = false"
            >
                <section>
                    <div class="text-center mb-5">
                        <p>{{ __('It appears you have a Local Library that’s not connected to your account. Here you can choose which one you want to keep. To keep both libraries separate, simply navigate to a different page.') }}</p>
                    </div>
                </section>

                <section class="flex flex-col justify-between gap-4 sm:flex-row">
                    <article
                        x-show="Object.keys(groupedItems).length !== 0"
                        x-cloak
                        x-on:librarydbloaded.window="getItemsGroupedByStatus()"
                        class="flex flex-col justify-between w-full pt-4 pr-4 pb-4 pl-4 border-2 rounded-lg"
                    >
                        <div class="space-y-4">
                            <h1 class="text-lg font-semibold">{{ __('Local Library') }}</h1>

                            <ul class="m-0 mb-4 list-none">
                                <!-- Iterate over model types -->
                                <template class="hidden" x-for="(typeData, modelType) in (groupedItems.toJSON ? groupedItems.toJSON() : groupedItems)" :key="modelType">
                                    <!-- Iterate over library status -->
                                    <li>
                                        <!-- You can customize this part based on the modelType -->
                                        <p x-text="modelType" class="text-secondary text-sm font-semibold"></p>

                                        <ul>
                                            <template x-for="(count, libraryStatus) in (typeData.toJSON ? typeData.toJSON() : typeData)" :key="libraryStatus">
                                                <li x-text="`${libraryStatus}: ${count}`"></li>
                                            </template>
                                        </ul>
                                    </li>
                                </template>
                            </ul>
                        </div>

                        <div class="flex flex-col items-center space-y-4">
                            <div class="flex flex-col w-full">
                                <p class="text-secondary text-sm font-semibold">{{ __('Last updated on:') }}</p>
                                <p x-text="formatUnixTimestamp(latestCreationDate)"></p>
                            </div>

                            <x-button x-on:click="openPopup('local')">{{ __('Keep this') }}</x-button>
                        </div>
                    </article>

                    <article
                        x-show="Object.keys(groupedItems).length === 0"
                        x-cloak
                        style="height: 512px"
                        class="flex flex-col justify-between w-full h-full pt-4 pr-4 pb-4 pl-4 bg-secondary border-2 border-primary rounded-lg"
                    >
                    </article>

                    <div class="flex items-center justify-center text-secondary font-bold">
                        <p class="uppercase">{{ __('or') }}</p>
                    </div>

                    <article
                        x-show="Object.keys(groupedItems).length === 0"
                        x-cloak
                        style="height: 512px"
                        class="flex flex-col justify-between w-full h-full pt-4 pr-4 pb-4 pl-4 bg-secondary border-2 rounded-lg"
                    >
                    </article>

                    <article
                        x-show="Object.keys(groupedItems).length !== 0"
                        class="flex flex-col justify-between w-full pt-4 pr-4 pb-4 pl-4 border-2 rounded-lg"
                    >
                        <div class="space-y-4">
                            <h1 class="text-lg font-semibold">{{ __(':x Library', ['x' => config('app.name')]) }}</h1>

                            <ul class="m-0 mb-4 list-none">
                                @foreach ($userLibrary as $type => $library)
                                    <li>
                                        @switch($type)
                                            @case(App\Models\Anime::class)
                                                <p class="text-secondary text-sm font-semibold">{{ __('Anime') }}</p>
                                                <ul>
                                                    @foreach ($library as $key => $item)
                                                        <li>
                                                            {{ $key }}: {{ $item['total'] }}
                                                        </li>
                                                    @endforeach
                                                </ul>

                                                @break
                                            @case(App\Models\Game::class)
                                                <p class="text-secondary text-sm font-semibold">{{ __('Game') }}</p>
                                                <ul>
                                                    @foreach ($library as $key => $item)
                                                        <li>
                                                            {{ $key }}: {{ $item['total'] }}
                                                        </li>
                                                    @endforeach
                                                </ul>

                                                @break
                                            @case(App\Models\Manga::class)
                                                <p class="text-secondary text-sm font-semibold">{{ __('Manga') }}</p>
                                                <ul>
                                                    @foreach ($library as $key => $item)
                                                        <li>
                                                            {{ $key }}: {{ $item['total'] }}
                                                        </li>
                                                    @endforeach
                                                </ul>

                                                @break
                                            @default
                                                @break
                                        @endswitch
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="flex flex-col items-center space-y-4">
                            <div class="flex flex-col w-full">
                                <p class="text-secondary text-sm font-semibold">{{ __('Last updated on:') }}</p>
                                <p>{{ $userLibrary->flatten(1)->pluck('updated_at')->max() }}</p>
                            </div>

                            <x-button x-on:click="openPopup('kurozora')">{{ __('Keep this') }}</x-button>
                        </div>
                    </article>

                    <div class="flex items-center justify-center text-secondary font-bold uppercase sm:hidden">
                        {{ __('or') }}
                    </div>
                </section>

                <div class="flex flex-col items-center mt-4">
                    <x-button
                        x-show="Object.keys(groupedItems).length !== 0"
                        x-cloak
                        x-on:click="openPopup('merge')"
                    >{{ __('Merge libraries') }}</x-button>

                    <x-button
                        x-show="Object.keys(groupedItems).length === 0"
                        x-cloak
                        disabled
                        style="width: 158px; height: 34px"
                    >
                    </x-button>
                </div>

                <x-dialog-modal id="merge-library-confirm" maxWidth="md">
                    <x-slot:title>
                        <span x-text="popups[popupType]?.title"></span>
                    </x-slot:title>

                    <x-slot:content>
                        <div class="pt-4 pb-4 pl-4 pr-4">
                            <p x-text="popups[popupType]?.message"></p>
                        </div>
                    </x-slot:content>

                    <x-slot:footer>
                        <x-outlined-button x-on:click="closePopup()" x-bind:disabled="busy">
                            {{ __('Cancel') }}
                        </x-outlined-button>

                        <x-button class="ml-2" x-show="popupType === 'local'" x-on:click="keepLocalLibrary()" x-bind:disabled="busy">
                            {{ __('Keep') }}
                        </x-button>

                        <x-button class="ml-2" x-show="popupType === 'kurozora'" x-on:click="keepKurozoraLibrary()" x-bind:disabled="busy">
                            {{ __('Keep') }}
                        </x-button>

                        <x-button class="ml-2" x-show="popupType === 'merge'" x-on:click="mergeLibraries()" x-bind:disabled="busy">
                            {{ str(__('Merge libraries'))->title() }}
                        </x-button>
                    </x-slot:footer>
                </x-dialog-modal>
            </div>
        </div>
    </main>
</x-base-layout>
