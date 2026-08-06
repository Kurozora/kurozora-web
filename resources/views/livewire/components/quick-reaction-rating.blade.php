<div class="flex gap-2 justify-center items-center">
    @foreach (\App\Enums\EmojiScore::getInstances() as $instance)
        <button
            class="pt-1 pr-1 pb-1 pl-1 transition ease-in-out duration-150 {{ $emojiScore !== null && $emojiScore !== $instance->value ? 'opacity-50' : '' }} {{ $disabled ? 'cursor-default' : 'hover:opacity-100' }}"
            title="{{ $instance->description }}"
            wire:click="rate({{ $instance->value }})"
            {{ $disabled ? 'disabled' : '' }}
        >
            <span class="flex items-center justify-center w-12 h-12 text-3xl leading-none rounded-full {{ $emojiScore === $instance->value ? 'bg-tinted' : '' }}">{{ $instance->emoji() }}</span>
        </button>
    @endforeach

    @if ($allowsRemove)
        <x-dialog-modal model="confirmingRemoval">
            <x-slot:title>
                {{ __('Remove Rating') }}
            </x-slot:title>

            <x-slot:content>
                <div class="pt-4 pb-4 pl-4 pr-4">
                    <p>{{ __('Removing your rating will also delete your review. Do you want to continue?') }}</p>
                </div>
            </x-slot:content>

            <x-slot:footer>
                <x-outlined-button wire:click="$toggle('confirmingRemoval')" wire:loading.attr="disabled">
                    {{ __('Nevermind') }}
                </x-outlined-button>

                <x-danger-button class="ml-2" wire:click="removeRating" wire:loading.attr="disabled">
                    {{ __('Remove') }}
                </x-danger-button>
            </x-slot:footer>
        </x-dialog-modal>
    @endif
</div>
