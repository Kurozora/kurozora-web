<x-action-section>
    <x-slot:title>
        {{ __('Active Sessions') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Manage and sign out your active sessions on other devices.') }}
    </x-slot:description>

    <x-slot:content>
        <div class="max-w-xl mb-4 text-sm text-primary">
            {{ __('If necessary, you may sign out of all of your sessions across all of your devices. If you feel your account has been compromised, you should also update your password.') }}
        </div>

        @if ($currentSession !== null)
            <div class="mb-4 p-3 rounded-lg bg-secondary">
                <x-lockups.session-lockup :session="$currentSession" />
            </div>
        @endif

        @if ($previewSessions->isNotEmpty())
            <div class="space-y-4">
                @foreach ($previewSessions as $session)
                    <x-lockups.session-lockup :session="$session" wire:key="preview-{{ $session->key }}" />
                @endforeach
            </div>
        @elseif ($currentSession === null)
            <div class="max-w-xl text-sm text-primary">
                {{ __('You aren’t signed in anywhere else.') }}
            </div>
        @endif

        <div class="flex items-center mt-5">
            <a href="{{ route('profile.settings.sessions') }}" wire:navigate>
                <x-outlined-button>
                    {{ __('See All') }}
                </x-outlined-button>
            </a>
        </div>
    </x-slot:content>
</x-action-section>
