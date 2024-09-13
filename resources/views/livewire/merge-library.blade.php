<main>
    <x-slot:title>
        {{ __('Merge Library') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Connect your local library with your Kurozora Account — the largest, free online anime, manga, game & music database.') }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Merge Library') }} — {{ config('app.name') }}" />
        <meta property="og:description"
              content="{{ __('Connect your local library with your Kurozora Account — the largest, free online anime, manga, game & music database.') }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('merge-library') }}">
    </x-slot:meta>

    <header class="bg-gray-100 shadow">
        <div class="flex max-w-7xl mx-auto pl-4 pr-4 py-6 sm:px-6">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Merge Library') }}
            </h2>
        </div>
    </header>

    <div
        x-data="{
            groupedItems: {},
            latestCreationDate: null,
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

                // Get the array based on the modelType, default to an empty array if not found
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

                // Initialize an array to store sorted entries
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

                            // Get the description based on the integer value
                            let descriptionKey = this.getStatusDescription(modelType, libraryStatus)

                            // Initialize the count if not present
                            this.groupedItems[modelType] = this.groupedItems[modelType] || {}
                            this.groupedItems[modelType][descriptionKey] = this.groupedItems[modelType][descriptionKey] || 0

                            // Increment the count for the specific library status
                            this.groupedItems[modelType][descriptionKey]++
                        }

                        if (Object.keys(this.groupedItems).length === 0) {
                            $wire.dispatch('local-library-empty')
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

                            $wire.dispatch('local-library-cleared', { merging: merging })
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
                                // Add the entry to the libraryData array
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
        class="flex flex-col justify-center w-screen max-w-prose mx-auto pl-4 pr-4 py-6 sm:px-6"
        x-on:clear-local-library.window="clearLocalLibrary(true)"
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
                                <p x-text="modelType" class="text-gray-400 text-sm font-semibold"></p>

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
                        <p class="text-gray-400 text-sm font-semibold">{{ __('Last updated on:') }}</p>
                        <p x-text="formatUnixTimestamp(latestCreationDate)"></p>
                    </div>

                    <x-button wire:click="togglePopupFor('local')">{{ __('Keep this') }}</x-button>
                </div>
            </article>

            <article
                x-show="Object.keys(groupedItems).length === 0"
                x-cloak
                style="height: 512px"
                class="flex flex-col justify-between w-full h-full pt-4 pr-4 pb-4 pl-4 bg-gray-200 border-2 rounded-lg"
            >
            </article>

            <div class="flex items-center justify-center text-gray-400 font-bold">
                <p>{{ __('OR') }}</p>
            </div>

            <article
                x-show="Object.keys(groupedItems).length === 0"
                x-cloak
                style="height: 512px"
                class="flex flex-col justify-between w-full h-full pt-4 pr-4 pb-4 pl-4 bg-gray-200 border-2 rounded-lg"
            >
            </article>

            <article
                x-show="Object.keys(groupedItems).length !== 0"
                class="flex flex-col justify-between w-full pt-4 pr-4 pb-4 pl-4 border-2 rounded-lg"
            >
                <div class="space-y-4">
                    <h1 class="text-lg font-semibold">{{ __('Kurozora Library') }}</h1>

                    <ul class="m-0 mb-4 list-none">
                        @foreach ($this->userLibrary as $type => $userLibrary)
                            <li>
                                @switch($type)
                                    @case(App\Models\Anime::class)
                                        <p class="text-gray-400 text-sm font-semibold">{{ __('Anime') }}</p>
                                        <ul>
                                            @foreach ($userLibrary as $key => $item)
                                                <li>
                                                    {{ $key }}: {{ $item['total'] }}
                                                </li>
                                            @endforeach
                                        </ul>

                                        @break
                                    @case(App\Models\Game::class)
                                        <p class="text-gray-400 text-sm font-semibold">{{ __('Game') }}</p>
                                        <ul>
                                            @foreach ($userLibrary as $key => $item)
                                                <li>
                                                    {{ $key }}: {{ $item['total'] }}
                                                </li>
                                            @endforeach
                                        </ul>

                                        @break
                                    @case(App\Models\Manga::class)
                                        <p class="text-gray-400 text-sm font-semibold">{{ __('Manga') }}</p>
                                        <ul>
                                            @foreach ($userLibrary as $key => $item)
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
                        <p class="text-gray-400 text-sm font-semibold">{{ __('Last updated on:') }}</p>
                        <p>{{ $this->userLibrary->flatten(1)->pluck('updated_at')->max() }}</p>
                    </div>

                    <x-button wire:click="togglePopupFor('kurozora')">{{ __('Keep this') }}</x-button>
                </div>
            </article>

            <div class="flex items-center justify-center text-gray-400 font-bold sm:hidden">
                {{ __('OR') }}
            </div>
        </section>

        <div class="flex flex-col items-center mt-4">
            <x-button
                x-show="Object.keys(groupedItems).length !== 0"
                x-cloak
                wire:click="togglePopupFor('merge')"
            >{{ __('Merge libraries') }}</x-button>

            <x-button
                x-show="Object.keys(groupedItems).length === 0"
                x-cloak
                disabled
                style="width: 158px; height: 34px"
            >
            </x-button>
        </div>

        <x-dialog-modal maxWidth="md" model="showPopup">
            <x-slot:title>
                {{ $popupData['title'] }}
            </x-slot:title>

            <x-slot:content>
                <p>{{ $popupData['message'] }}</p>
            </x-slot:content>

            <x-slot:footer>
                <x-button wire:click="$toggle('showPopup')">{{ __('Cancel') }}</x-button>

                @switch ($selectedPopupType)
                    @case ('local')
                        <x-button x-on:click="getLocalLibraryJSON().then(data => $wire.overwriteLibrary(data))">{{ __('Keep') }}</x-button>
                        @break
                    @case ('kurozora')
                        <x-button x-on:click="clearLocalLibrary(false)">{{ __('Keep') }}</x-button>
                        @break
                    @case ('merge')
                        <x-button x-on:click="getLocalLibraryJSON().then(data => $wire.mergeLibrary(data))">{{ __('Merge Libraries') }}</x-button>
                        @break
                    @default
                        @break
               @endswitch
            </x-slot:footer>
        </x-dialog-modal>
    </div>
</main>
