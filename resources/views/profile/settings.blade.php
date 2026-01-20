<x-app-layout>
    <x-slot:title>
        {{ __('Settings') }}
    </x-slot:title>

    <x-slot:header>
        <h2 class="text-2xl font-bold">
            {{ __(':x Account', ['x' => config('app.name')]) }}
        </h2>
    </x-slot:header>

    <div class="xl:safe-area-inset">
        <div class="pl-4 pr-4 py-6">
            <livewire:profile.update-account-information-form :user="$user" />

            @if ($user === auth()->user())
                <div class="pt-10">
                    <livewire:profile.update-profile-information-form />
                </div>
            @endif

            @if ($user->parent_id === null)
                <div class="pt-10">
                    <livewire:profile.select-preferred-child-form :user="$user" />
                </div>
            @endif

            <div class="pt-10">
                <livewire:profile.select-preferred-language-form :user="$user" />
            </div>

            <div class="pt-10">
                <livewire:profile.select-preferred-timezone-form :user="$user" />
            </div>

            <div class="pt-10">
                <livewire:profile.select-preferred-tv-rating-form :user="$user" />
            </div>

            @if ($user === auth()->user())
                <div class="pt-10">
                    <livewire:profile.import-library-form />
                </div>

                <div class="pt-10">
                    <livewire:profile.subscribe-to-reminders />
                </div>

                <div class="pt-10">
                    <livewire:profile.update-password-form />
                </div>

                <div class="pt-10">
                    <livewire:profile.two-factor-authentication-form />
                </div>

                <div class="pt-10">
                    <livewire:profile.sign-out-app-sessions-form />
                </div>

                <div class="pt-10">
                    <livewire:profile.sign-out-other-sessions-form />
                </div>

                <div class="pt-10">
                    <livewire:profile.delete-library-form />
                </div>

                <div class="pt-10">
                    <livewire:profile.delete-user-form />
                </div>
            @else
                <div class="pt-10">
                    <livewire:profile.unlink-user-form :user="$user" />
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
