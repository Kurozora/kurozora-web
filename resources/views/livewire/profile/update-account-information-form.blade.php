<x-form-section submit="updateAccountInformation">
    <x-slot:title>
        {{ __('Account Information') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Update your account’s information, such as username and email address.') }}
    </x-slot:description>

    <x-slot:form>
        {{-- Slug --}}
        <div class="col-span-3 sm:col-span-2">
            <x-label for="username" value="{{ __('Username') }}" />
            <x-input id="username" type="text" class="mt-1 block w-full {{ ($this->user->is_subscribed || $this->user->can_change_username) ?: 'select-none opacity-25' }}" wire:model="state.username" autocomplete="username" disabled="{{ !($this->user->is_subscribed || $this->user->can_change_username) }}" />
            <x-input-error for="username" class="mt-2" />
        </div>

        {{-- Email --}}
        <div class="col-span-6 sm:col-span-4">
            <x-label for="email" value="{{ __('Email') }}" />
            <x-input id="email" type="email" class="mt-1 block w-full" wire:model="state.email" />
            <x-input-error for="email" class="mt-2" />
        </div>
    </x-slot:form>

    <x-slot:actions>
        <x-action-message class="mr-3" on="saved">
            {{ __('Saved.') }}
        </x-action-message>

        <x-button wire:loading.attr="disabled">
            {{ __('Save') }}
        </x-button>
    </x-slot:actions>
</x-form-section>
