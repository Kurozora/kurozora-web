<x-form-section submit="updateProfileInformation">
    <x-slot:title>
        {{ __('Profile Information') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Update your account’s profile information, such as nickname and profile image.') }}
    </x-slot:description>

    <x-slot:header>
        {{-- Banner Image --}}
        <div x-data="{bannerImageName: null, bannerImagePreview: @entangle('bannerImage').live}" class="col-span-6 sm:col-span-6">
            {{-- Banner Image File Input --}}
            <input
                type="file" class="hidden"
                wire:model.live="bannerImage"
                x-ref="bannerImage"
                x-on:change="
                    bannerImageName = $refs.bannerImage.files[0].name;
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        bannerImagePreview = e.target.result;
                    };
                    reader.readAsDataURL($refs.bannerImage.files[0]);
               "
            />

            {{-- Current Banner Image --}}
            <div
                class="relative"
                x-show="!bannerImagePreview"
            >
                <livewire:components.banner-image-view :user="$this->user" />

                <div class="absolute top-0 right-0 bottom-0 left-0 flex justify-center bg-black/20">
                    <div class="flex items-center justify-center">
                        <button
                            class="inline-flex items-center justify-center w-10 h-10 pt-2 pr-2 pb-2 pl-2 text-white rounded-full transition duration-150 ease-in-out hover:bg-white/20 focus:outline-none focus:bg-white/60 sm:w-12 sm:h-12"
                            x-on:click.prevent="$refs.bannerImage.click()"
                        >
                            @svg('camera', 'fill-current', ['width' => '24'])
                        </button>

                        @if (!empty($this->user->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner())))
                            <button
                                class="inline-flex items-center justify-center w-10 h-10 pt-2 pr-2 pb-2 pl-2 text-red-500 rounded-full transition duration-150 ease-in-out hover:bg-white/20 focus:outline-none focus:bg-white/60 sm:w-12 sm:h-12"
                                wire:click="deleteBannerImage"
                            >
                                @svg('trash', 'fill-current', ['width' => '24'])
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            {{-- New Banner Image Preview --}}
            <div
                class="relative"
                x-show="bannerImagePreview"
                x-cloak=""
            >
                <div class="flex">
                    <picture class="relative w-full overflow-hidden">
                        <img
                            class="inline-block w-full h-40 object-cover sm:h-80"
                            style="background-color: {{ $this->user->getFirstMedia(\App\Enums\MediaCollection::Banner)?->custom_properties['background_color'] ?? '#FF9300' }}"
                            src=""
                            x-bind:src="bannerImagePreview"
                            alt="{{ $this->user->username }} Banner Image"
                        >

                        <div class="absolute top-0 left-0 h-full w-full"></div>
                    </picture>
                </div>

                <div class="absolute top-0 right-0 bottom-0 left-0 flex justify-center bg-black/20">
                    <div class="flex items-center justify-center">
                        <button
                            class="inline-flex items-center justify-center w-12 h-12 pt-2 pr-2 pb-2 pl-2 text-white rounded-full hover:bg-white/20 focus:outline-none focus:bg-white/60 transition duration-150 ease-in-out"
                            x-on:click.prevent="$refs.bannerImage.click()"
                        >
                            @svg('camera', 'fill-current text-white', ['width' => '24'])
                        </button>

                        @if (!empty($this->user->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner())))
                            <button
                                class="inline-flex items-center justify-center w-12 h-12 pt-2 pr-2 pb-2 pl-2 text-red-500 rounded-full hover:bg-white/20 focus:outline-none focus:bg-white/60 transition duration-150 ease-in-out"
                                wire:click="deleteBannerImage"
                            >
                                @svg('trash', 'fill-current text-red-500', ['width' => '24'])
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <x-input-error for="bannerImage" class="mt-2" />
        </div>

        {{-- Profile Image --}}
        <div x-data="{profileImageName: null, profileImagePreview: @entangle('profileImage').live}" class="-mt-14 pl-4 pr-4 col-span-6 z-10 sm:-mt-20 sm:col-span-6">
            {{-- Profile Image File Input --}}
            <input
                type="file" class="hidden"
                wire:model.live="profileImage"
                x-ref="profileImage"
                x-on:change="
                    profileImageName = $refs.profileImage.files[0].name;
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        profileImagePreview = e.target.result;
                    };
                    reader.readAsDataURL($refs.profileImage.files[0]);
                "
            />

            {{-- Current Profile Image --}}
            <div
                class="relative w-16 h-16 rounded-full overflow-hidden sm:w-24 sm:h-24"
                x-show="!profileImagePreview"
            >
                <livewire:components.profile-image-view :user="$this->user" />

                <div class="absolute top-0 right-0 bottom-0 left-0 flex justify-center bg-black/20">
                    <div class="flex items-center justify-center">
                        <button
                            class="inline-flex items-center justify-center w-8 h-8 pt-2 pr-2 pb-2 pl-2 text-white rounded-full transition duration-150 ease-in-out hover:bg-white/20 focus:outline-none focus:bg-white/60 sm:w-12 sm:h-12"
                            x-on:click.prevent="$refs.profileImage.click()"
                        >
                            @svg('camera', 'fill-current', ['width' => '24'])
                        </button>

                        @if (!str_starts_with($this->user->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()), 'https://ui-avatars.com/'))
                            <button
                                class="inline-flex items-center justify-center w-8 h-8 pt-2 pr-2 pb-2 pl-2 text-red-500 rounded-full transition duration-150 ease-in-out hover:bg-white/20 focus:outline-none focus:bg-white/60 sm:w-12 sm:h-12"
                                wire:click="deleteProfileImage"
                            >
                                @svg('trash', 'fill-current', ['width' => '24'])
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            {{-- New Profile Image Preview --}}
            <div
                class="relative w-16 h-16 rounded-full overflow-hidden sm:w-24 sm:h-24"
                x-show="profileImagePreview"
                x-cloak=""
            >
                <img class="w-16 h-16 border-2 border-black/5 rounded-full sm:w-24 sm:h-24"
                     src=""
                     x-bind:src="profileImagePreview"
                     alt="{{ $this->user->username }} Profile Image"
                >

                <div class="absolute top-0 right-0 bottom-0 left-0 flex justify-center bg-black/20">
                    <div class="flex items-center justify-center">
                        <button
                            class="inline-flex items-center justify-center w-8 h-8 pt-2 pr-2 pb-2 pl-2 text-white rounded-full transition duration-150 ease-in-out hover:bg-white/20 focus:outline-none focus:bg-white/60 sm:w-12 sm:h-12"
                            x-on:click.prevent="$refs.profileImage.click()"
                        >
                            @svg('camera', 'fill-current', ['width' => '24'])
                        </button>

                        @if (!str_starts_with($this->user->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()), 'https://ui-avatars.com/'))
                            <button
                                class="inline-flex items-center justify-center w-8 h-8 pt-2 pr-2 pb-2 pl-2 text-red-500 rounded-full transition duration-150 ease-in-out hover:bg-white/20 focus:outline-none focus:bg-white/60 sm:w-12 sm:h-12"
                                wire:click="deleteProfileImage"
                            >
                                @svg('trash', 'fill-current', ['width' => '24'])
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <x-input-error for="profileImage" class="mt-2" />
        </div>
    </x-slot:header>

    <x-slot:form>
        {{-- Nickname --}}
        <div class="col-span-6">
            <x-label for="nickname" value="{{ __('Display Name') }}" />
            <x-input id="nickname" type="text" class="mt-1 block w-full" placeholder="{{ $this->user->slug }}" wire:model="state.nickname" autocomplete="nickname" />
            <x-input-error for="nickname" class="mt-2" />
        </div>

        {{-- Biography --}}
        <div class="col-span-6">
            <x-label for="biography" value="{{ __('Biography') }}" />
            <x-textarea id="biography" class="mt-1 block w-full" placeholder="{{ __('Describe yourself') }}" :autoresize="true" wire:model="state.biography" />
            <x-input-error for="biography" class="mt-2" />
        </div>
    </x-slot:form>

    <x-slot:actions>
        <x-action-message class="mr-3" on="saved">
            {{ __('Saved.') }}
        </x-action-message>

        <x-button wire:loading.attr="disabled" wire:target="profileImage,bannerImage">
            {{ __('Save') }}
        </x-button>
    </x-slot:actions>
</x-form-section>
