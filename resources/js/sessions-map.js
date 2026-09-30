class SessionsMap {
    #selector = '[data-sessions-map]'
    #source = 'https://cdn.apple-mapkit.com/mk/x/mapkit.core.js'
    #readyEvent = 'kurozora:mapkit-ready'

    constructor() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.#prepare())
        } else {
            this.#prepare()
        }

        document.addEventListener('livewire:navigated', () => this.#prepare())
    }

    #prepare() {
        const element = document.querySelector(this.#selector)

        if (!element) {
            return
        }

        if (window.kurozoraMapKitReady || window.mapkit) {
            this.#render(element)
            return
        }

        this.#inject()
        document.addEventListener(this.#readyEvent, () => this.#render(element), { once: true })
    }

    #inject() {
        if (document.querySelector(`script[src="${this.#source}"]`)) {
            return
        }

        window.kurozoraMapKitLoaded = () => {
            window.kurozoraMapKitReady = true
            document.dispatchEvent(new CustomEvent(this.#readyEvent))
        }

        const script = document.createElement('script')

        script.src = this.#source
        script.crossOrigin = 'anonymous'
        script.async = true
        script.dataset.libraries = 'map,annotations'
        script.dataset.callback = 'kurozoraMapKitLoaded'

        document.head.append(script)
    }

    #render(element) {
        const token = element.dataset.token

        if (!window.mapkit || !token || element.dataset.mapRendered) {
            return
        }

        if (!window.kurozoraMapKitInitialized) {
            window.mapkit.init({
                authorizationCallback: (done) => done(token),
            })
            window.kurozoraMapKitInitialized = true
        }

        element.dataset.mapRendered = 'true'

        const map = new window.mapkit.Map(element, {
            showsUserLocation: false,
            showsPointsOfInterest: false,
            showsMapTypeControl: false,
            showsCompass: window.mapkit.FeatureVisibility.Hidden,
        })

        const annotations = JSON.parse(element.dataset.coordinates ?? '[]').map((coordinate) =>
            new window.mapkit.MarkerAnnotation(
                new window.mapkit.Coordinate(coordinate.latitude, coordinate.longitude),
                { title: coordinate.title, subtitle: coordinate.subtitle }
            )
        )

        if (annotations.length > 0) {
            map.showItems(annotations)
            return
        }

        map.region = new window.mapkit.CoordinateRegion(
            new window.mapkit.Coordinate(36.2048, 138.2529),
            new window.mapkit.CoordinateSpan(16, 16)
        )
    }
}

new SessionsMap()
