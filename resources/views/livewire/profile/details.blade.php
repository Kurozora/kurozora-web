<main>
    <x-slot name="title">
        {{ $user->username }}
    </x-slot>

    <x-slot name="meta">
        <meta property="og:title" content="{{ $user->username . __(' on Kurozora') }}" />
        <meta property="og:description" content="{{ $user->biography }}" />
        <meta property="og:image" content="{{ $user->profile_image_url }}" />
        <meta property="og:type" content="profile" />
        <meta property="og:profile:username" content="{{ $user->username }}" />
    </x-slot>

    <x-slot name="appArgument">
        users/{{ $user->id }}
    </x-slot>

    <div>
        <section>
            @livewire('components.banner-image-view', ['user' => $user])
        </section>

        <section class="relative max-w-7xl mx-auto px-4 py-6 z-10 sm:px-6">
            <div class="flex items-end justify-between -mt-20">
                <div class="flex items-end">
                    @livewire('components.profile-image-view', ['user' => $user])

                    <span class="flex items-baseline">
                        <p class="ml-2 text-xl font-bold">{{ $user->username }}</p>
                        @switch ($user->getActivityStatus()->value)
                            @case(\App\Enums\UserActivityStatus::Online || \App\Enums\UserActivityStatus::SeenRecently)
                                <span class="block ml-1 w-2 h-2 bg-green-500 rounded-full"></span>
                                @break
                            @case(\App\Enums\UserActivityStatus::Offline)
                                <span class="block ml-1 w-2 h-2 bg-red-500 rounded-full"></span>
                        @endswitch
                    </span>
                </div>

                @auth
                    @if ($user->id == Auth::user()->id)
                        <x-button wire:click="$toggle('showPopup')">{{ __('Edit') }}</x-button>
                    @else
                        <x-button>
                            @if ($user->followers()->where('user_id', Auth::user()->id)->exists())
                                {{ __('✓︎ Following') }}
                            @else
                                {{ __('+ Follow') }}
                            @endif
                        </x-button>
                    @endif
                @endif
            </div>

            @if ($isEditing)
                <x-textarea class="mt-2" :readonly="$isEditing">{{ $user->biography }}</x-textarea>
            @else
                <div class="mt-2 py-2 px-3">{!! nl2br($user->biography) !!}</div>
            @endif

            <div class="flex justify-between">
                <x-profile-information-badge>
                    <x-slot name="title">{{ __('Reputation') }}</x-slot>
                    <x-slot name="description">0</x-slot>
                </x-profile-information-badge>

                <x-profile-information-badge>
                    <x-slot name="title">{{ __('Badges') }}</x-slot>
                    <x-slot name="description">{{ $user->badges()->count() }}</x-slot>
                </x-profile-information-badge>

                <x-profile-information-badge>
                    <x-slot name="title">{{ __('Following') }}</x-slot>
                    <x-slot name="description">{{ $user->following()->count() }}</x-slot>
                </x-profile-information-badge>

                @livewire('components.followers-badge', ['user' => $user])
            </div>

            <x-hr class="mt-2" />
        </section>
    </div>

    @auth
        <x-modal-form-section wire:model="showPopup" submit="">
            <x-slot name="title">
                {{ __('Edit Profile') }}
            </x-slot>

            @livewire('profile.update-profile-information-form')
        </x-modal-form-section>
    @endauth
</main>
