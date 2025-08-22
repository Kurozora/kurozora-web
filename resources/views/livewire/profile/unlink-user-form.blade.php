<x-action-section>
    <x-slot:title>
        {{ __('Disconnect Child Account') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Permanently disconnect your child’s account.') }}
    </x-slot:description>

    <x-slot:content>
        <div class="max-w-xl text-sm text-primary">
            <p>{{ __('Once your child’s account is disconnected, you can no longer control it. You will be asked for your password to confirm the deletion.') }}</p>

            <br />

            <p>{{ __('To delete your child’s account instead, sign-in to their account and use the account deletion form in the settings.') }}</p>
        </div>

        <div class="mt-5">
            <x-danger-button wire:click="confirmUserDisconnect" wire:loading.attr="disabled">
                {{ __('Disconnect Account') }}
            </x-danger-button>
        </div>

        <!-- Disconnect Confirmation Modal -->
        <x-dialog-modal model="confirmingUserDisconnect">
            <x-slot:title>
                {{ __('Disconnect Child Account') }}
            </x-slot:title>

            <x-slot:content>
                <div class="pt-4 pb-4 pl-4 pr-4">
                    <p>{{ __('Are you sure you want to disconnect :x’s account? Enter your password to confirm.', ['x' => $this->user->username]) }}</p>

                    <div class="mt-4" x-data="{}" x-on:confirming-disconnect-user.window="setTimeout(() => $refs.password.focus(), 250)">
                        <x-input type="password" class="mt-1 block w-3/4" placeholder="{{ __('Password') }}"
                                 x-ref="password"
                                 wire:model="password"
                                 wire:keydown.enter="disconnectUser" />

                        <x-input-error for="password" class="mt-2" />
                    </div>
                </div>
            </x-slot:content>

            <x-slot:footer>
                <x-outlined-button wire:click="$toggle('confirmingUserDisconnect')" wire:loading.attr="disabled">
                    {{ __('Cancel') }}
                </x-outlined-button>

                <x-danger-button class="ml-2" wire:click="disconnectUser" wire:loading.attr="disabled">
                    {{ __('Disconnect Child') }}
                </x-danger-button>
            </x-slot:footer>
        </x-dialog-modal>
    </x-slot:content>
</x-action-section>
