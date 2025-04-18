<x-form-section submit="subscribeToReminders">
    <x-slot:title>
        {{ __('Subscribe to Reminders') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Integrate your anime schedule into your calendar.') }}
    </x-slot:description>

    <x-slot:form>
        <div class="col-span-12">
            <div class="max-w-xl text-sm text-primary">
                <p>{{ __('Never miss an episode again with reminders for new airings. Simply click the "Subscribe" button below or copy and paste the link into your preferred calendar app to sync with your reminders.') }}</p>
                <br />
                <p>{{ __('Once added, authenticate with :x by providing your username and password when prompted. After that, your calendar app will regularly check for updates and show reminders for episodes up to 2 weeks ahead of time. Reminders are refreshed once a day, ensuring you are always up-to-date on the latest episodes!', ['x' => config('app.name')]) }}</p>
                <br />
                {{ __('Please note that an active :x+ subscription is required to use this feature.', ['x' => config('app.name')]) }}
            </div>

            <br />

            <x-input class="w-full" type="text" value="{{ route('api.me.reminders.download') }}" readonly />
        </div>
    </x-slot:form>

    <x-slot:actions>
        <x-button>
            {{ __('Subscribe') }}
        </x-button>
    </x-slot:actions>
</x-form-section>
