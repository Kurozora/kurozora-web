<x-dialog-modal maxWidth="md" model="showPopup">
    <x-slot:title>
        {{ __('Write a Review') }}
    </x-slot:title>

    <x-slot:content>
        <div class="flex flex-col gap-4">
            <div class="flex flex-col gap-2">
                <div class="flex items-center">
                    <p class="">{{ __('Click to Rate:') }}</p>

                    <livewire:components.star-rating :model-id="$modelID" :model-type="$modelType" :rating="$userRating?->rating" :star-size="'md'" />
                </div>

                <x-textarea class="block w-full h-48 mt-1 resize-none" placeholder="{{ __('What’s on your mind?') }}" wire:model="reviewText"></x-textarea>
            </div>

            <div class="flex flex-col gap-2">
                <p class="text-gray-500 text-sm font-semibold">{{ __('Private Notes') }}</p>

                <x-textarea class="block w-full h-48 mt-1 resize-none" placeholder="{{ __('What’s on your mind?') }}" wire:model="noteText"></x-textarea>
            </div>
        </div>
    </x-slot:content>

    <x-slot:footer>
        <x-button wire:click="submitReview">{{ __('Submit') }}</x-button>
    </x-slot:footer>
</x-dialog-modal>
