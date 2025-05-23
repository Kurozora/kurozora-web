<x-app-layout>
    <x-slot:title>
        {{ __('Sign In') }}
    </x-slot:title>

    <x-slot:meta>
        <meta name="appleid-signin-client-id" content="{{ config('services.apple.client_id') }}">
        <meta name="appleid-signin-scope" content="name email">
        <meta name="appleid-signin-redirect-uri" content="{{ route('siwa.callback') }}">
        <meta name="appleid-signin-state" content="{{ Str::random(40) }}">
    </x-slot:meta>

    <x-slot:header>
        <h2 class="text-2xl font-bold">
            {{ __(':x Account', ['x' => config('app.name')]) }}
        </h2>
    </x-slot:header>

    <div class="flex flex-col justify-center w-screen h-full max-w-prose mx-auto pt-4 pb-6 pl-4 pr-4">
        {{-- Header --}}
        <section>
            <div class="text-center mb-5">
                <h1 class="text-2xl font-bold">{{ __('Welcome to :x!', ['x' => config('app.name')]) }}</h1>
                <p>{{ __('Sign in with your :x Account to use the library and other :x services.', ['x' => config('app.name')]) }}</p>
            </div>
        </section>

        {{-- Errors --}}
        <section>
            <x-validation-errors class="mb-4" />
        </section>

        {{-- Status messages --}}
        <section>
            @if (session('status'))
                <div class="mb-4 font-medium text-sm text-green-600">
                    {{ session('status') }}
                </div>
            @endif
        </section>

        {{-- Form --}}
        <section>
            <form method="POST" action="{{ route('sign-in') }}">
                @csrf
                <x-honey recaptcha="sign_in" />

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

                <section class="space-y-4">
                    <div>
                        <x-label for="email" value="{{ __('Email') }}" />
                        <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" placeholder="{{ __('Your cool email address 🙌') }}" required autofocus />
                    </div>

                    <div>
                        <x-label for="password" value="{{ __('Password') }}" />
                        <x-input id="password" class="block mt-1 w-full" type="password" name="password" placeholder="{{ __('Your super secret password 👀') }}" required autocomplete="current-password" />
                    </div>
                </section>

                <section class="flex flex-wrap justify-between gap-2 mt-4">
                    <label for="remember_me" class="flex items-center">
                        <input id="remember_me" type="checkbox" class="h-4 w-4 rounded focus:ring-0 focus:ring-offset-0 text-tint focus:border-tint" name="remember">
                        <span class="ml-2 text-sm text-secondary">{{ __('Remember me') }}</span>
                    </label>

                    @if (Route::has('password.request'))
                        <x-link class="text-sm" href="{{ route('password.request') }}" wire:navigate>
                            {{ __('Forgot your password? Let’s reset it 📧') }}
                        </x-link>
                    @endif
                </section>

                <section class="flex flex-col items-center justify-end gap-4 mt-8 text-center">
                    <x-button>
                        {{ __('Open sesame 👐') }}
                    </x-button>
                </section>
            </form>

            <div class="flex flex-col items-center justify-end gap-4 mt-4 text-center">
                <p class="tracking-wide font-black">{{ __('———— or ————') }}</p>

                <x-link class="text-sm" href="{{ route('sign-up') }}" wire:navigate>
                    {{ __('New to :x? Join us 🔥', ['x' => config('app.name')]) }}
                </x-link>

                <p class="tracking-wide font-black">{{ __('———— or ————') }}</p>

                <div>
                    <x-auth.apple-button />
                </div>
            </div>
        </section>

        {{-- Services --}}
        <section class="flex flex-col items-center space-y-4 mt-16 text-center">
            <x-picture class="max-w-sm">
                <img src="{{ asset('images/static/promotional/kurozora_services.webp') }}" alt="{{ __(':x services', ['x' => config('app.name')]) }}" title="{{ __(':x services', ['x' => config('app.name')]) }}">
            </x-picture>

            <p class="text-sm">{{ __('Your :x Account lets you access your library, favorites, reminders, reviews, and more on your devices, automatically.', ['x' => config('app.name')]) }}</p>
        </section>

        {{-- Legal --}}
        <section class="space-y-1 mt-16 text-center text-sm">
            <p class="text-secondary">{{ __('Your :x Account information is used to enable :x services when you sign in. :x services includes the library where you can keep track of the shows you are interested in.', ['x' => config('app.name')]) }}</p>
            <x-link href="{{ route('legal.privacy-policy') }}" wire:navigate>{{ __('See how your data is managed…') }}</x-link>
        </section>
    </div>
</x-app-layout>
