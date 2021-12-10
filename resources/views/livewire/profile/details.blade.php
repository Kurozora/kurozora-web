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
                <a href="#" class="w-48 text-center">
                    <p class="font-semibold">0</p>
                    <p class="text-gray-500 text-sm font-semibold">{{ __('Reputation') }}</p>
                </a>
                <a href="#" class="w-48 text-center">
                    <p class="font-semibold">{{ $user->badges()->count() }}</p>
                    <p class="text-gray-500 text-sm font-semibold">{{ __('Badges') }}</p>
                </a>
                <a href="#" class="w-48 text-center">
                    <p class="font-semibold">{{ $user->following()->count() }}</p>
                    <p class="text-gray-500 text-sm font-semibold">{{ __('Following') }}</p>
                </a>
                <a href="#" class="w-48 text-center">
                    <p class="font-semibold">{{ $user->followers()->count() }}</p>
                    <p class="text-gray-500 text-sm font-semibold">{{ __('Followers') }}</p>
                </a>
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
