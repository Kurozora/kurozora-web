(function() {
    initializeLibraryDatabase()

    function initializeLibraryDatabase() {
        // Open or create the IndexedDB database
        let request = indexedDB.open('library', 1)

        // Handle database upgrade (create object store)
        request.onupgradeneeded = function(event) {
            let libraryDB = event.target.result
            libraryDB.createObjectStore('libraryData', { keyPath: 'id' })
        }

        // Handle successful database opening
        request.onsuccess = function(event) {
            window.libraryDB = event.target.result
            window.dispatchEvent(new Event('librarydbloaded'))
        }.bind(this)

        // Handle errors
        request.onerror = function(event) {
            console.error('Error opening IndexedDB for library database:', event.target.error)
        }
    }
})()
