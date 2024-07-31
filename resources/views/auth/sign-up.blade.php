<x-app-layout>
    <x-slot:title>
        {{ __('Sign Up') }}
    </x-slot:title>

    <x-slot:header>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kurozora Account') }}
        </h2>
    </x-slot:header>

    <div class="flex flex-col justify-center w-screen h-full max-w-prose mx-auto pl-4 pr-4 py-6 sm:px-6">
        <div class="mb-5 text-center">
            <h1 class="text-2xl font-bold">{{ __('New to Kurozora?') }}</h1>
            <p>{{ __('Create an account and join the community.') }}</p>
        </div>

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('sign-up') }}">
            @csrf
            <x-honey recaptcha="sign_up" />

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
                class="block mt-1 w-full"
                type="hidden"
                name="hasLocalLibrary"
                value="0"
                x-model="hasLocalLibrary"
            />

            <section class="space-y-4" >
                <div>
                    <x-label for="username" value="{{ __('Username') }}" />
                    <x-input id="username" class="mt-1 block w-full" type="text" name="username" :value="old('username')" placeholder="{{ __('Pick a cool one') }} 🙈" required autofocus autocomplete="username" />
                </div>

                <div>
                    <x-label for="email" value="{{ __('Email') }}" />
                    <x-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email')" placeholder="{{ __('We all forget our passwords') }} 🙉" required />
                </div>

                <div>
                    <x-label for="password" value="{{ __('Password') }}" />
                    <x-input id="password" class="mt-1 block w-full" type="password" name="password" placeholder="{{ __('Make it super secret') }} 🙊" required autocomplete="new-password" passwordrules="minlength: 5; maxlength: 255; required: lower; required: upper; required: digit; required: special;" />
                </div>

                <div>
                    <x-label for="password_confirmation" value="{{ __('Confirm Password') }}" />
                    <x-input id="password_confirmation" class="mt-1 block w-full" type="password" name="password_confirmation" placeholder="{{ __('But keep it memorable') }} 🐵" required autocomplete="new-password" passwordrules="minlength: 5; maxlength: 255; required: lower; required: upper; required: digit; required: special;" />
                </div>
            </section>

            <section class="flex flex-col items-center justify-end gap-4 mt-8 text-center">
                <x-button>
                    {{ __('Join') }} 🤗
                </x-button>

                <p class="tracking-wide font-black">{{ __('———— or ————') }}</p>

                <x-link class="text-sm" href="{{ route('sign-in') }}" wire:navigate>
                    {{ __('Already have an account? Let’s sign in') }} 🔥
                </x-link>
            </section>
        </form>

        {{-- Services --}}
        <section class="flex flex-col items-center space-y-4 mt-16 text-center">
            <x-picture class="max-w-sm">
                <img src="{{ asset('images/static/promotional/kurozora_services.webp') }}" alt="Kurozora services" title="Kurozora services">
            </x-picture>

            <p class="text-sm">{{ __('Your Kurozora Account lets you access your library, favorites, reminders, reviews, and more on your devices, automatically.') }}</p>
        </section>

        {{-- Legal --}}
        <section class="space-y-1 mt-16 text-center text-sm">
            <p class="text-gray-500">{{ __('Your Kurozora Account information is used to enable Kurozora services when you sign in. Kurozora services includes the library where you can keep track of the shows you are interested in.') }}</p>
            <x-link href="{{ route('legal.privacy-policy') }}" wire:navigate>{{ __('See how your data is managed...') }}</x-link>
        </section>
    </div>
</x-app-layout>
