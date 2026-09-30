<div
    x-data="reportModal({{ Js::from(['modal' => 'review-report', 'event' => 'review-report', 'reason' => App\Enums\ReportReason::NotAReview, 'otherReason' => App\Enums\ReportReason::Other]) }})"
    x-on:review-report-modal.window="open($event.detail)"
    x-on:review-reported.window="settle($event.detail)"
    x-on:review-report-failed.window="fail($event.detail)"
    x-on:user-actions-failed.window="busy = false"
>
    <x-dialog-modal id="review-report">
        <x-slot:title>
            {{ __('Report Review') }}
        </x-slot:title>

        <x-slot:content>
            <div class="pt-4 pb-4 pl-4 pr-4 space-y-4">
                <div>
                    <label class="block text-sm font-semibold mb-2">{{ __('Reason') }}</label>

                    <x-select x-model="reason">
                        @foreach (App\Enums\ReportReason::offeredForReview() as $reasonKey)
                            <option value="{{ $reasonKey }}">{{ App\Enums\ReportReason::getDescription($reasonKey) }}</option>
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
