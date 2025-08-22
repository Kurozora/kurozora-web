<x-action-section>
    <x-slot:title>
        {{ __('Family') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Invite and manage child accounts for children 13 and younger.') }}
    </x-slot:description>

    <x-slot:content>
        <div class="max-w-xl text-sm text-primary">
            {{ __('Instead of sharing an account with your child, which can give them unwanted access to your personal data, create a :x Account for them. Then you can easily set age-based parental controls, and they can use Family Sharing, and other :x services.', ['x' => config('app.name')]) }}
        </div>

        <!-- Invitation Form -->
        <div class="mt-5">
            <x-input label="{{ __('Invite Child') }}" type="email" wire:model="email" placeholder="{{ __('Child’s Email') }}" />

            <x-button wire:click="inviteChild" wire:loading.attr="disabled" class="mt-2">
                {{ __('Send Invitation') }}
            </x-button>

            <x-input-error for="email" class="mt-2" />
        </div>

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
