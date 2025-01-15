<x-action-section>
    <x-slot:title>
        {{ __('Family') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Invite and manage child accounts for children 13 and younger.') }}
    </x-slot:description>

    <x-slot:content>
        <div class="max-w-xl text-sm text-primary">
            {{ __('Instead of sharing an account with your child, which can give them unwanted access to your personal data, create a Kurozora Account for them. Then you can easily set age-based parental controls, and they can use Family Sharing, and other Kurozora services.') }}
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
        <div class="mt-5">
            <ul class="m-0 mb-4 space-y-4 list-none">
                @forelse ($children as $child)
                    <li class="flex flex-nowrap justify-between">
                        <div class="flex items-center">
                            <picture
                                class="relative shrink-0 w-12 aspect-square rounded-full overflow-hidden"
                                style="background-color: {{ $user->getFirstMedia(\App\Enums\MediaCollection::Profile)?->custom_properties['background_color'] ?? 'var(--bg-secondary-color)' }};"
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
                                <p class="leading-tight line-clamp-2">{{ $child->username }}</p>
                                <p class="text-xs leading-tight opacity-75">{{ __('@:x', ['x' => $child->slug]) }}</p>
                            </div>
                        </div>

                        <div class="flex flex-row flex-nowrap items-center justify-between z-10 whitespace-nowrap">
                            <x-link-button href="{{ route('profile.settings.user', $child) }}">
                                {{ __('View Profile') }}
                            </x-link-button>

                            <x-danger-button wire:click="confirmChildDeletion({{ $child->id }})">
                                {{ __('Unlink') }}
                            </x-danger-button>
                        </div>
                    </li>
                @empty
                    <li class="text-secondary">{{ __('Invite or create a child account to see it here.') }}</li>
                @endforelse
            </ul>
        </div>

        <!-- Delete Confirmation Modal -->
        <x-dialog-modal model="confirmingChildDeletion">
            <x-slot:title>
                {{ __('Unlink Child Account') }}
            </x-slot:title>

            <x-slot:content>
                {{ __('Are you sure you want to unlink this child account? Enter your password to confirm.') }}

                <div class="mt-4" x-data="{}" x-on:confirming-delete-child.window="setTimeout(() => $refs.password.focus(), 250)">
                    <x-input type="password" class="mt-1 block w-3/4" placeholder="{{ __('Password') }}"
                             x-ref="password"
                             wire:model="password"
                             wire:keydown.enter="unlinkChild" />

                    <x-input-error for="password" class="mt-2" />
                </div>
            </x-slot:content>

            <x-slot:footer>
                <x-outlined-button wire:click="$toggle('confirmingChildDeletion')" wire:loading.attr="disabled">
                    {{ __('Cancel') }}
                </x-outlined-button>

                <x-danger-button wire:click="unlinkChild" wire:loading.attr="disabled">
                    {{ __('Unlink Child') }}
                </x-danger-button>
            </x-slot:footer>
        </x-dialog-modal>
    </x-slot:content>
</x-action-section>
