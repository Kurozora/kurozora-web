<x-dialog-modal model="confirmingReport">
    <x-slot:title>
        {{ __('Report Entry') }}
    </x-slot:title>

    <x-slot:content>
        <div class="pt-4 pb-4 pl-4 pr-4 space-y-4">
            <div>
                <label class="block text-sm font-semibold mb-2">{{ __('Reason') }}</label>

                <x-select wire:model.live="reportReasonKey">
                    @foreach (App\Enums\ParentalGuideReportReason::getInstances() as $reason)
                        <option value="{{ $reason->value }}">{{ $reason->description }}</option>
                    @endforeach
                </x-select>

                <x-input-error for="reportReasonKey" class="mt-1" />
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">
                    {{ __('Details') }}
                    @if ($reportReasonKey === App\Enums\ParentalGuideReportReason::Other)
                        <span class="text-red-500">*</span>
                    @endif
                </label>

                <x-textarea
                    wire:model="reportDetails"
                    rows="4"
                    maxlength="1000"
                    placeholder="{{ $reportReasonKey === App\Enums\ParentalGuideReportReason::Other ? __('Tell us more') : __('Tell us more (optional)') }}"
                />
                <x-input-error for="reportDetails" class="mt-1" />
            </div>
        </div>
    </x-slot:content>

    <x-slot:footer>
        <x-outlined-button wire:click="$toggle('confirmingReport')" wire:loading.attr="disabled">
            {{ __('Cancel') }}
        </x-outlined-button>

        <x-button class="ml-2" wire:click="submitReport" wire:loading.attr="disabled">
            {{ __('Submit') }}
        </x-button>
    </x-slot:footer>
</x-dialog-modal>
