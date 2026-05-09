if ('serviceWorker' in navigator) {
    window.addEventListener('load', async () => {
        const registrations = await navigator.serviceWorker.getRegistrations()
        await Promise.all(
            registrations
                .filter(registration => new URL(registration.scope).pathname.startsWith('/build/'))
                .map(registration => registration.unregister())
        )

        try {
            const registration = await navigator.serviceWorker.register('/service-worker.js', {
                scope: '/',
            })
            console.log('Service worker registered:', registration)
        } catch (error) {
            console.error('Error registering service worker:', error)
        }
    })
}
