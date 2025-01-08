<x-app-layout>
    <x-slot:title>
        {{ __('Settings') }}
    </x-slot:title>

    <x-slot:header>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kurozora Account') }}
        </h2>
    </x-slot:header>

    <div class="max-w-7xl mx-auto pl-4 pr-4 py-6 space-y-10 sm:px-6">
        @if ($user === auth()->user())
            <livewire:profile.update-account-information-form />

            <x-hr class="hidden sm:block" />

            <div class="sm:mt-0">
                <livewire:profile.update-profile-information-form />
            </div>

            <x-hr class="hidden sm:block" />
        @endif

        @if ($user->parent_id === null)
{{--            <div class="sm:mt-0">--}}
{{--                <livewire:profile.select-preferred-child-form :user="$user" />--}}
{{--            </div>--}}

{{--            <x-hr class="hidden sm:block" />--}}
        @endif

        <div class="sm:mt-0">
            <livewire:profile.select-preferred-language-form :user="$user" />
        </div>

        <x-hr class="hidden sm:block" />

        <div class="sm:mt-0">
            <livewire:profile.select-preferred-timezone-form :user="$user" />
        </div>

        <x-hr class="hidden sm:block" />

        <div class="sm:mt-0">
            <livewire:profile.select-preferred-tv-rating-form :user="$user" />
        </div>

        @if ($user === auth()->user())
            <x-hr class="hidden sm:block" />

            <div class="sm:mt-0">
                <livewire:profile.import-library-form />
            </div>

            <x-hr class="hidden sm:block" />

            <div class="sm:mt-0">
                <livewire:profile.subscribe-to-reminders />
            </div>

            <x-hr class="hidden sm:block" />

            <div class="sm:mt-0">
                <livewire:profile.update-password-form />
            </div>

            <x-hr class="hidden sm:block" />

            <div class="sm:mt-0">
                <livewire:profile.two-factor-authentication-form />
            </div>

            <x-hr class="hidden sm:block" />

            <div class="sm:mt-0">
                <livewire:profile.sign-out-app-sessions-form />
            </div>

            <x-hr class="hidden sm:block" />

            <div class="sm:mt-0">
                <livewire:profile.sign-out-other-sessions-form />
            </div>

            <x-hr class="hidden sm:block" />

            <div class="sm:mt-0">
                <livewire:profile.delete-library-form />
            </div>

            <x-hr class="hidden sm:block" />

            <div class="sm:mt-0">
                <livewire:profile.delete-user-form />
            </div>
        @endif
    </div>
</x-app-layout>
