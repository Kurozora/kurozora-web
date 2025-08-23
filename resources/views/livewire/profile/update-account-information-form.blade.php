<x-form-section
    submit="updateAccountInformation"
    x-data="{
        countdown: {{ $rateLimitDecay }},
        interval: null
    }"
>
    <x-slot:title>
        {{ __('Account Information') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Update your account’s information, such as username and email address.') }}
    </x-slot:description>

    <x-slot:form>
        {{-- Slug --}}
        <div class="col-span-3 sm:col-span-2">
            <x-label for="username" value="{{ __('Username') }}" />
            <x-input id="username" type="text" class="mt-1 block w-full {{ ($this->user->is_subscribed || $this->user->can_change_username) ?: 'select-none opacity-25' }}" autocomplete="username" placeholder="{{ $state['username'] }}" disabled="{{ !($this->user->is_subscribed || $this->user->can_change_username) }}" wire:model="state.username" />
            <x-input-error for="username" class="mt-2" />
        </div>

        {{-- Email --}}
        <div class="col-span-6 sm:col-span-4">
            <x-label for="email" value="{{ __('Email') }}" />
            <x-input id="email" type="email" class="mt-1 block w-full" autocomplete="email" placeholder="{{ $state['email'] }}" wire:model="state.email" x-bind:disabled="countdown > 0" />
            <x-input-error for="email" class="mt-2" />
        </div>

        {{-- Verify email --}}
        @if (!$hasVerifiedEmail)
            <div class="col-span-6">
                <div class="max-w-xl text-sm text-primary">
                    <p>{{ __('Account is pending verification. Could you verify your email address by clicking the link we sent you? If you didn’t receive the email, please double-check that the address you entered is correct, and check both your inbox and spam folder. If needed, we’ll gladly send you another.') }}</p>
                    <br >
                </div>
            </div>
        @endif
    </x-slot:form>

    <x-slot:actions>
        <div
            class="flex items-center justify-end"
            x-data="{
                startCountdown({ countdown }) {
                    if (this.interval) {
                        clearInterval(this.interval)
                    }

                    this.countdown = countdown

                    this.interval = setInterval(() => {
                        if (this.countdown > 0) {
                            this.countdown--
                        } else {
                            clearInterval(this.interval)
                        }
                    }, 1000)
                },
                init() {
                    $wire.on('verification-link-sent', (countdown) => this.startCountdown(countdown))

                    if (this.countdown != 0) {
                        this.startCountdown({countdown: this.countdown})
                        $nextTick(() => {
                           $wire.dispatch('saved')
                       })
                    }
                }
            }"
            x-init="init()"
        >
            <x-action-message class="mr-3" on="saved" :is-ephemeral="$hasVerifiedEmail">
                <template x-if="countdown > 0">
                    <p>{{ $hasVerifiedEmail ? __('Saved.') : __('Retry in:') }} (<span x-text="countdown"></span>)</p>
                </template>

                <template x-if="countdown === 0">
                    <p>{{ $hasVerifiedEmail ? __('Saved.') : '' }}</p>
                </template>
            </x-action-message>

            <x-button
                wire:loading.attr="disabled"
                x-bind:disabled="countdown > 0"
            >
                {{ $hasVerifiedEmail ? __('Save') : __('Resend Email') }}
            </x-button>
        </div>
    </x-slot:actions>
</x-form-section>
