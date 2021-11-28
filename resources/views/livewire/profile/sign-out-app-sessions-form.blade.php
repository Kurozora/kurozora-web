<x-action-section>
    <x-slot name="title">
        {{ __('App Sessions') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Manage and sign out your active app sessions on other devices.') }}
    </x-slot>

    <x-slot name="content">
        <div class="max-w-xl text-sm text-gray-600">
            {{ __('If necessary, you may sign out of all of your app sessions across all of your devices. If you feel your account has been compromised, you should also update your password.') }}
        </div>

        @if (count($this->tokens) > 0)
            <div class="mt-5 space-y-6">
                <!-- App Sessions -->
                @foreach ($this->tokens as $token)
                    <div class="flex items-center">
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round" class="w-8 h-8 text-gray-500">
                                <path d="M0 0h24v24H0z" stroke="none"></path><rect x="7" y="4" width="10" height="16" rx="1"></rect><path d="M11 5h2M12 17v.01"></path>
                            </svg>
                        </div>

                        <div class="ml-3">
                            <div class="text-sm text-gray-600">
                                {{ $token->name }}
                            </div>

                            <div>
                                <div class="text-xs text-gray-500">
                                    {{ __('Last active') }} {{ $token->last_activity }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="flex items-center mt-5">
            <x-button wire:click="confirmSignOut" wire:loading.attr="disabled">
                {{ __('Sign Out App Sessions') }}
            </x-button>

            <x-action-message class="ml-3" on="signedOut">
                {{ __('Done.') }}
            </x-action-message>
        </div>

        <!-- Sign Out App Devices Confirmation Modal -->
        <x-dialog-modal wire:model="confirmingSignOut">
            <x-slot name="title">
                {{ __('Sign Out App Sessions') }}
            </x-slot>

            <x-slot name="content">
                {{ __('Please enter your password to confirm you would like to sign out of your app sessions across all of your devices.') }}

                <div class="mt-4" x-data="{}" x-on:confirming-sign-out-app-sessions.window="setTimeout(() => $refs.password.focus(), 250)">
                    <x-input type="password" class="mt-1 block w-3/4" placeholder="{{ __('Password') }}"
                             x-ref="password"
                             wire:model.defer="password"
                             wire:keydown.enter="signOutAppSessions" />

                    <x-input-error for="password" class="mt-2" />
                </div>
            </x-slot>

            <x-slot name="footer">
                <x-outlined-button wire:click="$toggle('confirmingSignOut')" wire:loading.attr="disabled">
                    {{ __('Nevermind') }}
                </x-outlined-button>

                <x-button class="ml-2" wire:click="signOutAppSessions" wire:loading.attr="disabled">
                    {{ __('Sign Out App Sessions') }}
                </x-button>
            </x-slot>
        </x-dialog-modal>
    </x-slot>
</x-action-section>
