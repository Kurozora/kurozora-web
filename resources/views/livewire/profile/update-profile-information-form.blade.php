<x-form-section submit="updateProfileInformation">
    <x-slot name="title">
        {{ __('Profile Information') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Update your account\'s profile information and email address.') }}
    </x-slot>

    <x-slot name="form">
        <!-- Profile Image -->
        <div x-data="{photoName: null, photoPreview: null}" class="col-span-6 sm:col-span-6">
            <!-- Profile Image File Input -->
            <input type="file" class="hidden"
                wire:model="photo"
                x-ref="photo"
                x-on:change="
                    photoName = $refs.photo.files[0].name;
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        photoPreview = e.target.result;
                    };
                    reader.readAsDataURL($refs.photo.files[0]);
            " />

            <x-label for="photo" value="{{ __('Photo') }}" />

            <!-- Current Profile Image -->
            <div class="mt-2" x-show="!photoPreview">
                <div class="h-20 w-20 bg-cover rounded-full" style="background-image: url({{ Auth::user()->profile_image_url }});" alt="{{ Auth::user()->username }}" role="img"></div>
            </div>

            <!-- New Profile Image Preview -->
            <div class="mt-2" x-show="photoPreview">
                <div class="block rounded-full w-20 h-20"
                      x-bind:style="'background-size: cover; background-repeat: no-repeat; background-position: center center; background-image: url(\'' + photoPreview + '\');'" role="img" alt="{{ Auth::user()->username }}">
                </div>
            </div>

            <x-outlined-button class="mt-2 mr-2" type="button" x-on:click.prevent="$refs.photo.click()">
                {{ __('Select A New Photo') }}
            </x-outlined-button>

            @if (!str_starts_with(Auth::user()->profile_image_url, 'https://ui-avatars.com/'))
                <x-danger-button type="button" class="mt-2" wire:click="deleteProfilePhoto">
                    {{ __('Remove Photo') }}
                </x-danger-button>
            @endif

            <x-input-error for="photo" class="mt-2" />
        </div>

        <!-- Username -->
        <div class="col-span-3 sm:col-span-2">
            <x-label for="username" value="{{ __('Username') }}" />
            <x-input id="username" type="text" class="mt-1 block w-full {{ settings('can_change_username') ?: 'select-none opacity-25' }}" wire:model.defer="state.username" autocomplete="username" disabled="{{ !settings('can_change_username') }}" />
            <x-input-error for="username" class="mt-2" />
        </div>

        <!-- Email -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="email" value="{{ __('Email') }}" />
            <x-input id="email" type="email" class="mt-1 block w-full" wire:model.defer="state.email" />
            <x-input-error for="email" class="mt-2" />
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-action-message class="mr-3" on="saved">
            {{ __('Saved.') }}
        </x-action-message>

        <x-button wire:loading.attr="disabled" wire:target="photo">
            {{ __('Save') }}
        </x-button>
    </x-slot>
</x-form-section>
