@props(['hasBorder' => false, 'backgroundColor' => 'black', 'borderRadius' => 15])

<form method="POST" action="{{ route('siwa.sign-in') }}">
    @csrf
    <x-input
        x-data="{
            hasLocalLibrary: 0,
            setHasLocalLibrary() {
                // Ensure the database is initialized
                if (!window.libraryDB) {
                    console.error('IndexedDB not initialized for library database.')
                    return false
                }

                // Start a transaction and get the object store
                let transaction = window.libraryDB.transaction(['libraryData'], 'readonly')
                let objectStore = transaction.objectStore('libraryData')

                // Open a cursor to count the number of entries
                let countRequest = objectStore.count()

                countRequest.onsuccess = function(event) {
                    let count = event.target.result

                    // Set if there are any entries in the object store
                    this.hasLocalLibrary = count > 0 ? 1 : 0
                }.bind(this)

                countRequest.onerror = function(event) {
                    console.error('Error counting entries in library database:', event.target.error)
                    return false
                }
            }
        }"
        x-on:librarydbloaded.window="setHasLocalLibrary()"
        id="has_local_library"
        type="hidden"
        name="hasLocalLibrary"
        value="0"
        x-model="hasLocalLibrary"
    />

    <button id="appleid-signin" class="w-52 h-8" data-color="{{ $backgroundColor }}" data-border="{{ $hasBorder }}" data-border-radius="{{ $borderRadius }}"></button>
    <script type="text/javascript" src="https://appleid.cdn-apple.com/appleauth/static/jsapi/appleid/1/en_US/appleid.auth.js"></script>
</form>
