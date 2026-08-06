<main>
    <x-slot:title>
        {{ __('Active Sessions') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Manage and sign out your active sessions on other devices.') }}
    </x-slot:description>

    <x-slot:meta>
        <meta name="robots" content="noindex" />
        <link rel="canonical" href="{{ route('profile.settings.sessions') }}">
    </x-slot:meta>

    @if (filled($mapToken))
        <section>
            <div wire:ignore id="sessions-map" class="w-full h-64 overflow-hidden bg-secondary"></div>
        </section>
    @endif

    <div
        class="pt-4 pb-6"
        x-data="{
            selectMode: false,
            selected: {},
            get selectedKeys() {
                return Object.keys(this.selected)
            },
            get hasSelection() {
                return this.selectedKeys.length > 0
            },
            get selectionCount() {
                return this.selectedKeys.length
            },
            get visibleRows() {
                return Array.from(this.$root.querySelectorAll('[data-session-key]'))
            },
            get allSelected() {
                const rows = this.visibleRows
                return rows.length > 0 && rows.every(row => this.selected[row.dataset.sessionKey] !== undefined)
            },
            get countLabel() {
                return this.hasSelection
                    ? @js(__(':count Selected')).replace(':count', this.selectionCount)
                    : @js(__('Select Sessions'))
            },
            isSelected(key) {
                return this.selected[key] !== undefined
            },
            toggleSelection(key) {
                if (this.selected[key] !== undefined) {
                    delete this.selected[key]
                } else {
                    this.selected[key] = true
                }
            },
            enterSelectMode() {
                this.selectMode = true
                this.selected = {}
            },
            exitSelectMode() {
                this.selectMode = false
                this.selected = {}
            },
            toggleSelectAll() {
                if (this.allSelected) {
                    this.selected = {}
                    return
                }
                this.visibleRows.forEach(row => {
                    this.selected[row.dataset.sessionKey] = true
                })
            },
            batchSignOut() {
                if (!this.hasSelection) return
                $wire.confirmSignOutSelected(this.selectedKeys)
            },
        }"
        @signed-out.window="exitSelectMode()"
    >
        <section class="mb-4 xl:safe-area-inset">
            <div class="flex flex-wrap items-baseline justify-between gap-2 pl-4 pr-4">
                <div class="flex flex-col">
                    <h1 class="text-2xl font-bold">{{ __('Active Sessions') }}</h1>

                    <p
                        class="text-sm text-secondary leading-5"
                        style="min-height: 1.25rem"
                        x-text="selectMode ? countLabel : ''"
                    ></p>
                </div>

                <div class="flex items-center gap-4">
                    <template x-if="!selectMode">
                        <div class="flex items-center gap-4">
                            <button
                                type="button"
                                class="text-sm text-tint hover:opacity-75 cursor-pointer"
                                x-on:click="enterSelectMode()"
                            >
                                {{ __('Select') }}
                            </button>

                            <button
                                type="button"
                                class="text-sm text-red-500 hover:opacity-75 cursor-pointer"
                                wire:click="confirmSignOutAll"
                            >
                                {{ __('Sign Out All') }}
                            </button>
                        </div>
                    </template>

                    <template x-if="selectMode">
                        <div class="flex items-center gap-4">
                            <button
                                type="button"
                                class="text-sm text-tint hover:opacity-75 cursor-pointer"
                                x-on:click="toggleSelectAll()"
                                x-text="allSelected ? @js(__('Deselect All')) : @js(__('Select All'))"
                            ></button>

                            <button
                                type="button"
                                class="text-sm text-red-500 hover:opacity-75 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed"
                                x-bind:disabled="!hasSelection"
                                x-on:click="batchSignOut()"
                            >
                                {{ __('Sign Out') }}
                            </button>

                            <button
                                type="button"
                                class="text-sm text-secondary hover:text-primary cursor-pointer"
                                x-on:click="exitSelectMode()"
                                aria-label="{{ __('Cancel') }}"
                                title="{{ __('Cancel') }}"
                            >
                                @svg('xmark', 'fill-current', ['width' => 14])
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </section>

        @if ($currentSession !== null)
            <section class="mb-6 xl:safe-area-inset">
                <h2 class="text-sm font-semibold text-secondary mb-2 pl-4 pr-4">{{ __('Current Session') }}</h2>

                <div class="bg-secondary rounded-xl pl-2 pr-2 pt-1 pb-1 ml-4 mr-4">
                    <x-lockups.session-lockup :session="$currentSession" />
                </div>
            </section>
        @endif

        @if ($otherSessions->isNotEmpty())
            <section class="xl:safe-area-inset">
                <h2 class="text-sm font-semibold text-secondary mb-2 pl-4 pr-4">{{ __('Other Sessions') }}</h2>

                <div class="bg-secondary rounded-xl pl-2 pr-2 pt-1 pb-1 ml-4 mr-4">
                    <ul class="flex flex-col m-0">
                        @foreach ($otherSessions as $key => $session)
                            <li
                                class="relative group rounded-md"
                                wire:key="session-{{ $session->key }}"
                                data-session-key="{{ $session->key }}"
                            >
                                <x-lockups.session-lockup :session="$session" :supports-select="true" />

                                <div
                                    class="absolute top-1 right-2 z-10 opacity-0 group-hover:opacity-100 transition-opacity duration-150"
                                    x-show="!selectMode"
                                >
                                    <div class="flex items-center gap-3 pl-2 pr-2 pt-1 pb-1 bg-secondary border border-primary rounded-md shadow-sm">
                                        <button
                                            type="button"
                                            class="text-xs text-red-500 hover:opacity-75 cursor-pointer"
                                            x-on:click="$wire.confirmSignOutSelected(['{{ $session->key }}'])"
                                        >
                                            {{ __('Sign Out') }}
                                        </button>
                                    </div>
                                </div>

                                @if ($key !== $otherSessions->count() - 1)
                                    <x-hr class="m-0" />
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </section>
        @endif

        <!-- Sign Out Confirmation Modal -->
        <x-dialog-modal model="confirmingSignOut">
            <x-slot:title>
                {{ __('Sign Out') }}
            </x-slot:title>

            <x-slot:content>
                <div class="pt-4 pb-4 pl-4 pr-4">
                    <p>{{ __('Please enter your password to confirm you would like to sign out of the selected sessions.') }}</p>

                    <div class="mt-4">
                        <x-input type="password" class="mt-1 block w-3/4" placeholder="{{ __('Password') }}"
                                 wire:model="password"
                                 wire:keydown.enter="signOut" />

                        <x-input-error for="password" class="mt-2" />
                    </div>
                </div>
            </x-slot:content>

            <x-slot:footer>
                <x-outlined-button wire:click="$toggle('confirmingSignOut')" wire:loading.attr="disabled">
                    {{ __('Nevermind') }}
                </x-outlined-button>

                <x-button class="ml-2" wire:click="signOut" wire:loading.attr="disabled">
                    {{ __('Sign Out') }}
                </x-button>
            </x-slot:footer>
        </x-dialog-modal>
    </div>

    @if (filled($mapToken))
        @assets
            <script>
                window.kurozoraMapKitLoaded = function () {
                    window.kurozoraMapKitReady = true
                    document.dispatchEvent(new CustomEvent('kurozora:mapkit-ready'))
                }
            </script>
            <script src="https://cdn.apple-mapkit.com/mk/x/mapkit.core.js"
                    crossorigin async
                    data-libraries="map,annotations"
                    data-callback="kurozoraMapKitLoaded"></script>
        @endassets

        @script
            <script>
                const sessionsMapToken = @js($mapToken);
                const sessionsCoordinates = @js($coordinates);

                const renderSessionsMap = () => {
                    const element = document.getElementById('sessions-map')

                    if (!window.mapkit || !element || !sessionsMapToken || element.dataset.mapRendered) {
                        return
                    }

                    if (!window.kurozoraMapKitInitialized) {
                        mapkit.init({
                            authorizationCallback: (done) => done(sessionsMapToken),
                        })
                        window.kurozoraMapKitInitialized = true
                    }

                    element.dataset.mapRendered = 'true'

                    const map = new mapkit.Map(element, {
                        showsUserLocation: false,
                        showsPointsOfInterest: false,
                        showsMapTypeControl: false,
                        showsCompass: mapkit.FeatureVisibility.Hidden,
                    })

                    const annotations = sessionsCoordinates.map((coordinate) =>
                        new mapkit.MarkerAnnotation(
                            new mapkit.Coordinate(coordinate.latitude, coordinate.longitude),
                            { title: coordinate.title, subtitle: coordinate.subtitle }
                        )
                    )

                    if (annotations.length > 0) {
                        map.showItems(annotations)
                    } else {
                        map.region = new mapkit.CoordinateRegion(
                            new mapkit.Coordinate(36.2048, 138.2529),
                            new mapkit.CoordinateSpan(16, 16)
                        )
                    }
                }

                if (window.kurozoraMapKitReady) {
                    renderSessionsMap()
                } else {
                    document.addEventListener('kurozora:mapkit-ready', renderSessionsMap, { once: true })
                }
            </script>
        @endscript
    @endif
</main>
