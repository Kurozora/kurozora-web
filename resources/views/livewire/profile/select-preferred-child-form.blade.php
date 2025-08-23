<x-action-section>
    <x-slot:title>
        {{ __('Family') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Child accounts can be created by a parent or legal guardian for children 12 and younger.') }}
    </x-slot:description>

    <x-slot:content>
        <div class="max-w-xl text-sm text-primary">
            {{ __('Instead of sharing an account with your child, which can give them unwanted access to your personal data, invite them to join your family or create a :x Account for them. Then you can easily set age-based parental controls, and they can use Family Sharing, and other :x services.', ['x' => config('app.name')]) }}
        </div>

        <!-- Invitation Form -->
        @if ($children->count() < 6)
            <div class="flex flex-col items-center justify-end gap-4 mt-5">
                <div class="w-full">
                    <x-label for="inviteEmail" value="{{ __('Email') }}" />
                    <x-input id="inviteEmail" label="{{ __('Invite Child') }}" type="email" class="mt-1 block w-full" autocomplete="email" wire:model="email" />
                    <x-input-error for="email" class="mt-2" />
                    <x-action-message class="mt-2" on="invitation-sent">
                        <p>{{ __('An invitation has been sent to the provided email address if an account exists.') }}</p>
                    </x-action-message>
                </div>

                <x-button wire:click="inviteAccount" wire:loading.attr="disabled">
                    {{ __('Send Invitation') }}
                </x-button>
            </div>

            <div class="flex flex-col items-center justify-end gap-4 mt-4 text-center">
                <p class="">{{ __('———— or ————') }}</p>

                <x-link-button href="{{ route('sign-up.child') }}" wire:navigate>
                    {{ __('Create Child Account') }}
                </x-link-button>
            </div>
        @endif

        <!-- Child Accounts List -->
        @if ($children->isNotEmpty())
            <div class="mt-10">
                <ul class="m-0 space-y-4 list-none">
                    @foreach ($children as $child)
                        <li class="flex flex-wrap gap-2 justify-between">
                            <div class="flex items-center">
                                <picture
                                    class="relative shrink-0 w-12 aspect-square rounded-full overflow-hidden"
                                    style="background-color: {{ $child->getFirstMedia(\App\Enums\MediaCollection::Profile)?->custom_properties['background_color'] ?? 'var(--bg-secondary-color)' }};"
                                >
                                    <img
                                        class="w-full h-full object-cover lazyload"
                                        data-sizes="auto"
                                        data-src="{{ $child->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) }}"
                                        alt="{{ $child->username }} Profile Image"
                                        title="{{ $child->username }}"
                                        width="{{ $child->getFirstMedia(\App\Enums\MediaCollection::Profile)?->custom_properties['width'] ?? 96 }}"
                                        height="{{ $child->getFirstMedia(\App\Enums\MediaCollection::Profile)?->custom_properties['height'] ?? 96 }}"
                                    >

                                    <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-full"></div>
                                </picture>

                                <div class="pl-2 pr-2">
                                    <p class="leading-tight line-clamp-2" title="{{ $child->username }}">{{ $child->username }}</p>

                                    @if (!$child->hasVerifiedEmail())
                                        <p class="text-xs text-red-500">{{ __('Pending verification') }}</p>
                                    @else
                                        <p class="text-xs leading-tight opacity-75">{{ __('@:x', ['x' => $child->slug]) }}</p>
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-row gap-2 items-center justify-between whitespace-nowrap">
                                <x-link-button href="{{ route('profile.settings.user', $child) }}">
                                    {{ __('Settings') }}
                                </x-link-button>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </x-slot:content>
</x-action-section>
