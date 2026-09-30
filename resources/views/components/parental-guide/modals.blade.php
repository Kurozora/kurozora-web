<div
    x-data="reportModal({{ Js::from([
        'modal' => 'parental-guide-report',
        'event' => 'parental-guide-report',
        'reason' => App\Enums\ParentalGuideReportReason::Inaccurate,
        'otherReason' => App\Enums\ParentalGuideReportReason::Other,
    ]) }})"
    x-on:parental-guide-report-modal.window="open($event.detail)"
    x-on:parental-guide-reported.window="settle($event.detail)"
    x-on:parental-guide-report-failed.window="fail($event.detail)"
    x-on:user-actions-failed.window="busy = false"
>
    <x-dialog-modal id="parental-guide-report">
        <x-slot:title>
            {{ __('Report Entry') }}
        </x-slot:title>

        <x-slot:content>
            <div class="pt-4 pb-4 pl-4 pr-4 space-y-4">
                <div>
                    <label class="block text-sm font-semibold mb-2">{{ __('Reason') }}</label>

                    <x-select x-model="reason">
                        @foreach (App\Enums\ParentalGuideReportReason::getInstances() as $reason)
                            <option value="{{ $reason->value }}">{{ $reason->description }}</option>
                        @endforeach
                    </x-select>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-2">
                        {{ __('Details') }}
                        <span class="text-red-500" x-show="requiresDetails" x-cloak>*</span>
                    </label>

                    <x-textarea
                        x-model="details"
                        rows="4"
                        maxlength="1000"
                        x-bind:placeholder="requiresDetails ? {{ Js::from(__('Tell us more')) }} : {{ Js::from(__('Tell us more (optional)')) }}"
                    />

                    <p class="mt-1 text-sm text-red-600" x-show="error" x-text="error" x-cloak></p>
                </div>
            </div>
        </x-slot:content>

        <x-slot:footer>
            <x-outlined-button x-on:click="close()" x-bind:disabled="busy">
                {{ __('Cancel') }}
            </x-outlined-button>

            <x-button class="ml-2" x-on:click="submit()" x-bind:disabled="busy">
                {{ __('Submit') }}
            </x-button>
        </x-slot:footer>
    </x-dialog-modal>
</div>

<div
    x-data="confirmModal({ modal: 'parental-guide-delete', event: 'parental-guide-delete' })"
    x-on:parental-guide-delete-modal.window="open($event.detail)"
    x-on:parental-guide-deleted.window="settle()"
    x-on:user-actions-failed.window="busy = false"
>
    <x-dialog-modal id="parental-guide-delete" maxWidth="md">
        <x-slot:title>
            {{ __('Delete Entry') }}
        </x-slot:title>

        <x-slot:content>
            <div class="pt-4 pb-4 pl-4 pr-4">
                <p>{{ __('Delete this entry?') }}</p>
            </div>
        </x-slot:content>

        <x-slot:footer>
            <x-outlined-button x-on:click="close()" x-bind:disabled="busy">
                {{ __('Cancel') }}
            </x-outlined-button>

            <x-button class="ml-2" x-on:click="confirm()" x-bind:disabled="busy">
                {{ __('Delete') }}
            </x-button>
        </x-slot:footer>
    </x-dialog-modal>
</div>
