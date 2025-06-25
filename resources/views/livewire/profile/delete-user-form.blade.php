<x-action-section>
    <x-slot:title>
        {{ __('Delete Account') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Permanently delete your account.') }}
    </x-slot:description>

    <x-slot:content>
        <div class="max-w-xl text-sm text-primary">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. You will be asked for your password to confirm the deletion.') }}
        </div>

        <div class="mt-5">
            <x-danger-button wire:click="confirmUserDeletion" wire:loading.attr="disabled">
                {{ __('Delete Account') }}
            </x-danger-button>
        </div>

        <!-- Delete User Confirmation Modal -->
        <x-dialog-modal model="confirmingUserDeletion">
            <x-slot:title>
                {{ __('Delete Account') }}
            </x-slot:title>

            <x-slot:content>
                <div class="pt-4 pb-4 pl-4 pr-4">
                    <p>{{ __('Are you sure you want to delete your account? Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}</p>

                    <div class="mt-4" x-data="{}" x-on:confirming-delete-user.window="setTimeout(() => $refs.password.focus(), 250)">
                        <x-input type="password" class="mt-1 block w-3/4" placeholder="{{ __('Password') }}"
                                     x-ref="password"
                                     wire:model="password"
                                     wire:keydown.enter="deleteUser" />

                        <x-input-error for="password" class="mt-2" />
                    </div>
                </div>
            </x-slot:content>

            <x-slot:footer>
                <x-outlined-button wire:click="$toggle('confirmingUserDeletion')" wire:loading.attr="disabled">
                    {{ __('Nevermind') }}
                </x-outlined-button>

                <x-danger-button class="ml-2" wire:click="deleteUser" wire:loading.attr="disabled">
                    {{ __('Delete Account') }}
                </x-danger-button>
            </x-slot:footer>
        </x-dialog-modal>
    </x-slot:content>
</x-action-section>
