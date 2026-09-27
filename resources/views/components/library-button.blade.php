@props(['model'])

@auth
    <livewire:components.library-button :model="$model" :wire:key="'library-button-' . $model->getMorphClass() . '-' . $model->id" />
@else
    @php
        $libraryStatuses = match ($model->getMorphClass()) {
            \App\Models\Anime::class => \App\Enums\UserLibraryStatus::asAnimeSelectArray(),
            \App\Models\Game::class => \App\Enums\UserLibraryStatus::asGameSelectArray(),
            \App\Models\Manga::class => \App\Enums\UserLibraryStatus::asMangaSelectArray(),
        };
    @endphp

    <div
        x-data="{
            id: '{{ $model->slug }}',
            libraryStatus: -1,
            libraryKind: '{{ class_basename($model->getMorphClass()) }}',
            fetchLibraryStatus() {
                // Ensure the database is initialized
                if (!libraryDB) {
                    console.error('IndexedDB for library database not initialized.')
                    return
                }

                // Start a transaction and get the object store
                let transaction = libraryDB.transaction(['libraryData'], 'readonly')
                let objectStore = transaction.objectStore('libraryData')

                let entryId = this.libraryKind + ',' + this.id

                // Retrieve the entry by ID
                let getRequest = objectStore.get(entryId)
                getRequest.onsuccess = function(event) {
                    let entry = event.target.result

                    // Check if the entry exists
                    if (entry) {
                        this.libraryStatus = entry.libraryCategory
                    }
                }.bind(this)

                getRequest.onerror = function(event) {
                    console.error('Error fetching entry from library database:', event.target.error)
                }
            },
            updateLibraryStatus() {
                // Ensure the database is initialized
                if (!libraryDB) {
                    console.error('IndexedDB for library database not initialized.')
                    return
                }

                // Start a transaction and get the object store
                let transaction = libraryDB.transaction(['libraryData'], 'readwrite')
                let objectStore = transaction.objectStore('libraryData')

                let entryId = this.libraryKind + ',' + this.id
                let currentDate = Math.floor(new Date().getTime() / 1000)

                if (this.libraryStatus <= -1) {
                    this.libraryStatus = -1

                    // If the selected library status is -1, remove the entry
                    let deleteRequest = objectStore.delete(entryId)
                    deleteRequest.onsuccess = function() {
                        console.log('Entry deleted successfully in library database.')
                    }
                    deleteRequest.onerror = function(event) {
                        console.error('Error deleting entry in library database:', event.target.error)
                    }
                } else {
                    let putRequest = objectStore.put({
                        id: entryId,
                        slug: this.id,
                        libraryKind: this.libraryKind,
                        libraryCategory: this.libraryStatus,
                        creationDate: currentDate
                    })
                    putRequest.onsuccess = function() {
                        console.log('Entry added/updated successfully in library database.')
                    }
                    putRequest.onerror = function(event) {
                        console.error('Error adding/updating entry in library database:', event.target.error)
                    }
                }
            }
        }"
        x-init="fetchLibraryStatus()"
    >
        <x-select-button rounded="full" chevronClass="w-4 h-4 btn-text-tinted sm:w-6 sm:h-6" class="w-24 pl-3 pr-5 pt-2 pb-2 bg-tint text-xs btn-text-tinted font-semibold border-0 shadow-md hover:bg-tint-800 active:bg-tint focus:ring-0 sm:w-32 sm:pl-3 sm:pr-7" x-model="libraryStatus" x-on:change="updateLibraryStatus()">
            <option value="-1" selected hidden disabled>{{ str(__('Add'))->upper() }}</option>

            @foreach ($libraryStatuses as $key => $libraryStatus)
                <option value="{{ $key }}">{{ __($libraryStatus) }}</option>
            @endforeach

            <option class="text-red-500" value="-2" x-show="(libraryStatus >= 0)">{{ __('Remove from Library') }}</option>
        </x-select-button>
    </div>
@endauth
