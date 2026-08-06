<x-dialog-modal model="confirmingSubmit">
    <x-slot:title>
        {{ __('Submit Evaluation') }}
    </x-slot:title>

    <x-slot:content>
        <div class="pt-4 pb-4 pl-4 pr-4 space-y-4">
            <div>
                <label class="block text-sm font-semibold mb-2">{{ __('Category') }}</label>

                <x-select wire:model.live="submitCategory">
                    <option value="">{{ __('Select a category…') }}</option>

                    @foreach (App\Enums\ParentalGuideCategory::getInstances() as $category)
                        <option value="{{ $category->value }}">{{ $category->description }}</option>
                    @endforeach
                </x-select>

                <x-input-error for="submitCategory" class="mt-1" />
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">{{ __('Severity') }}</label>

                <x-select wire:model.live="submitRating">
                    @foreach (App\Enums\ParentalGuideRating::getInstances() as $rating)
                        <option value="{{ $rating->value }}">{{ $rating->description }}</option>
                    @endforeach
                </x-select>

                <x-input-error for="submitRating" class="mt-1" />
            </div>

            @php
                $hasSeverity = (int) $submitRating !== App\Enums\ParentalGuideRating::None;
                $supportsDepiction = $hasSeverity
                    && $submitCategory !== null
                    && in_array((int) $submitCategory, [
                        App\Enums\ParentalGuideCategory::SexAndNudity,
                        App\Enums\ParentalGuideCategory::ViolenceAndGore,
                        App\Enums\ParentalGuideCategory::FrighteningAndIntenseScenes,
                    ], true);
            @endphp

            @if ($hasSeverity)
                <div>
                    <label class="block text-sm font-semibold mb-2">{{ __('Frequency') }}</label>

                    <x-select wire:model="submitFrequency">
                        @foreach (App\Enums\ParentalGuideFrequency::getInstances() as $frequency)
                            <option value="{{ $frequency->value }}">{{ $frequency->key }}</option>
                        @endforeach
                    </x-select>

                    <x-input-error for="submitFrequency" class="mt-1" />
                </div>
            @endif

            @if ($supportsDepiction)
                <div>
                    <label class="block text-sm font-semibold mb-2">{{ __('Depiction') }}</label>
                    <x-select wire:model="submitDepiction">
                        @foreach (App\Enums\ParentalGuideDepiction::getInstances() as $depiction)
                            <option value="{{ $depiction->value }}">{{ $depiction->key }}</option>
                        @endforeach
                    </x-select>
                    <x-input-error for="submitDepiction" class="mt-1" />
                </div>
            @endif

            <div>
                <label class="block text-sm font-semibold mb-2">{{ __('Reason') }}</label>
                <x-textarea wire:model="submitReason" rows="4" placeholder="{{ __('Describe what stood out…') }}" />
                <x-input-error for="submitReason" class="mt-1" />
            </div>

            <label class="flex items-center gap-2">
                <x-checkbox wire:model="submitIsSpoiler" />
                <span class="text-sm">{{ __('Spoiler') }}</span>
            </label>
        </div>
    </x-slot:content>

    <x-slot:footer>
        <x-outlined-button wire:click="$toggle('confirmingSubmit')" wire:loading.attr="disabled">
            {{ __('Cancel') }}
        </x-outlined-button>

        <x-button class="ml-2" wire:click="submitEntry" wire:loading.attr="disabled">
            {{ __('Submit') }}
        </x-button>
    </x-slot:footer>
</x-dialog-modal>
