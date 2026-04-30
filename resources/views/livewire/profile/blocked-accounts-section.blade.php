<x-action-section>
    <x-slot:title>
        {{ __('Blocked Accounts') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('When you block someone, they will be able to see your public messages, but will no longer be able to engage with them. They will also not be able to follow or message you, and you will not see notifications from them.') }}
    </x-slot:description>

    <x-slot:content>
        @if ($this->blockedCount > 0)
            <div class="space-y-4">
                @foreach ($this->blockedUsers as $blockedUser)
                    <a class="flex items-center gap-3" href="{{ route('profile.details', $blockedUser) }}" wire:navigate>
                        <picture
                            class="relative shrink-0 w-10 h-10 rounded-full overflow-hidden"
                            style="background-color: {{ $blockedUser->getFirstMedia(\App\Enums\MediaCollection::Profile)?->custom_properties['background_color'] ?? 'var(--bg-secondary-color)' }};"
                        >
                            <img
                                class="w-full h-full object-cover lazyload"
                                data-sizes="auto"
                                data-src="{{ $blockedUser->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) }}"
                                alt="{{ $blockedUser->username }} {{ __('Profile Image') }}"
                                title="{{ $blockedUser->username }}"
                                width="{{ $blockedUser->getFirstMedia(\App\Enums\MediaCollection::Profile)?->custom_properties['width'] ?? 40 }}"
                                height="{{ $blockedUser->getFirstMedia(\App\Enums\MediaCollection::Profile)?->custom_properties['height'] ?? 40 }}"
                            >

                            <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-full"></div>
                        </picture>

                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-primary truncate">{{ $blockedUser->username }}</p>
                            <p class="text-xs text-secondary truncate">&#64;{{ $blockedUser->slug }}</p>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="flex items-center mt-5">
                <a href="{{ route('profile.blocked', $user) }}" wire:navigate>
                    <x-outlined-button>
                        {{ __('See All') }}
                    </x-outlined-button>
                </a>
            </div>
        @else
            <div class="max-w-xl text-sm text-primary">
                {{ __('You haven’t blocked anyone yet.') }}
            </div>
        @endif
    </x-slot:content>
</x-action-section>
