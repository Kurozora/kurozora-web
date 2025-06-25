<x-action-section wire:init="loadSection">
    <x-slot:title>
        {{ __('Browser Sessions') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Manage and sign out your active sessions on other browsers and devices.') }}
    </x-slot:description>

    <x-slot:content>
        <div class="max-w-xl text-sm text-primary">
            {{ __('If necessary, you may sign out of all of your other sessions across all of your devices. If you feel your account has been compromised, you should also update your password.') }}
        </div>

        @if (count($this->sessions) > 0)
            <div class="mt-5 space-y-6">
                <!-- Other Sessions -->
                @foreach ($this->sessions as $session)
                    <div class="flex items-center">
                        <div>
                            @if ($session->browser->isDesktop())
                                <svg fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor" class="w-8 h-8 text-secondary">
                                    <path d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round" class="w-8 h-8 text-secondary">
                                    <path d="M0 0h24v24H0z" stroke="none"></path><rect x="7" y="4" width="10" height="16" rx="1"></rect><path d="M11 5h2M12 17v.01"></path>
                                </svg>
                            @endif
                        </div>

                        <div class="ml-3">
                            <div class="text-sm text-secondary">
                                {{ $session->device_model . ' ' . __('on') . ' ' . $session->platform . ' ' . $session->platform_version . ' ' }}
                            </div>

                            <div>
                                <div class="text-xs text-secondary">
                                    {{ $session->ip_address }},

                                    @if ($session->is_current_device)
                                        <span class="text-green-500 font-semibold">{{ __('This device') }}</span>
                                    @else
                                        {{ __('Last active') }} {{ $session->last_activity }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="flex items-center mt-5">
            <x-button wire:click="confirmSignOut" wire:loading.attr="disabled">
                {{ __('Sign Out Other Sessions') }}
            </x-button>

            <x-action-message class="ml-3" on="signedOutBrowser">
                {{ __('Done.') }}
            </x-action-message>
        </div>

        <!-- Sign Out Other Devices Confirmation Modal -->
        <x-dialog-modal model="confirmingSignOut">
            <x-slot:title>
                {{ __('Sign Out Other Sessions') }}
            </x-slot:title>

            <x-slot:content>
                <div class="pt-4 pb-4 pl-4 pr-4">
                    <p>{{ __('Please enter your password to confirm you would like to sign out of your other browser sessions across all of your devices.') }}</p>

                    <div class="mt-4" x-data="{}" x-on:confirming-sign-out-other-browser-sessions.window="setTimeout(() => $refs.password.focus(), 250)">
                        <x-input type="password" class="mt-1 block w-3/4" placeholder="{{ __('Password') }}"
                                     x-ref="password"
                                     wire:model="password"
                                     wire:keydown.enter="signOutOtherBrowserSessions" />

                        <x-input-error for="password" class="mt-2" />
                    </div>
                </div>
            </x-slot:content>

            <x-slot:footer>
                <x-outlined-button wire:click="$toggle('confirmingSignOut')" wire:loading.attr="disabled">
                    {{ __('Nevermind') }}
                </x-outlined-button>

                <x-button class="ml-2" wire:click="signOutOtherBrowserSessions" wire:loading.attr="disabled">
                    {{ __('Sign Out Other Sessions') }}
                </x-button>
            </x-slot:footer>
        </x-dialog-modal>
    </x-slot:content>
</x-action-section>
