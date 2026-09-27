<div class="flex gap-0.5 justify-center">
    @if ($allowsRemove)
        @php
            $star0 = uniqid('star-0');
        @endphp

        <label for="{{ $star0 }}">
            <input id="{{ $star0 }}" class="hidden" type="radio" name="rating" value="-1" wire:model.live="rating" wire:change="rate" {{ $disabled ? 'disabled' : '' }} />

            @svg('star_rating', 'w-4 ' . $starSize . ' my-auto fill-current text-transparent ' . ($disabled ? '' : 'cursor-pointer'))
        </label>
    @endif

    @for ($i = 0; $i < 5; $i++)
        <div class="relative">
            @php($id = uniqid('star-' . $i + 0.5 ))
            <label class="absolute left-0 w-1/2 overflow-hidden" for="{{ $id }}" style="z-index: 1;">
                <input id="{{ $id }}" class="hidden" type="radio" name="rating" value="{{ $i + 0.5 }}" wire:model.live="rating" wire:change="rate" {{ $disabled ? 'disabled' : '' }} />

                @svg('star_rating', 'relative ' . $starSize . ' ' . ($rating >= $i + 0.5 ? 'text-tint' : 'text-transparent') . ' ' . ($disabled ? '' : 'cursor-pointer'), ['fill' => 'currentColor', 'stroke-width' => '1', 'stroke' => 'var(--tint-color)'])
            </label>

            @php($id = uniqid('star-' . $i + 1))
            <label for="{{ $id }}">
                <input id="{{ $id }}" class="hidden" type="radio" name="rating" value="{{ $i + 1 }}" wire:model.live="rating" wire:change="rate" {{ $disabled ? 'disabled' : '' }} />

                @svg('star_rating', 'relative ' . $starSize . ' ' . ($rating >= $i + 1 ? 'text-tint' : 'text-transparent') . ' ' . ($disabled ? '' : 'cursor-pointer'), ['fill' => 'currentColor', 'stroke-width' => '1', 'stroke' => 'var(--tint-color)'])
            </label>
        </div>
    @endfor

    @if (!$disabled)
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

    <x-dialog-modal model="showingRatingRestriction" maxWidth="md">
        <x-slot:title>
            {{ __('Rating Failed') }}
        </x-slot:title>

        <x-slot:content>
            <div class="pt-4 pb-4 pl-4 pr-4">
                <p>{{ $ratingRestriction }}</p>
            </div>
        </x-slot:content>

        <x-slot:footer>
            <x-button wire:click="$toggle('showingRatingRestriction')">{{ __('OK') }}</x-button>
        </x-slot:footer>
    </x-dialog-modal>
</div>
