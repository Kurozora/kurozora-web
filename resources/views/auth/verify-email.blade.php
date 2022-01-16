<x-app-layout>
    <x-slot name="title">
        {{ __('Verify Email') }}
    </x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kurozora ID') }}
        </h2>
    </x-slot>

    <div class="flex flex-col justify-center w-screen h-full max-w-prose mx-auto px-4 py-6 sm:px-6">
        <div class="text-center mb-5">
            <p class="text-2xl font-bold">{{ __('Thanks for signing up!') }}</p>
            <p>{{ __('Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn’t receive the email, we will gladly send you another.') }}</p>
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ __('A new verification link has been sent to the email address you provided during the sign up.') }}
            </div>
        @endif

        <div class="mt-4 flex items-center justify-between">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <x-honey recaptcha="verify_email_send" />

                <div>
                    <x-button type="submit">
                        {{ __('Resend Verification Email') }}
                    </x-button>
                </div>
            </form>

            <form method="POST" action="{{ route('sign-out') }}">
                @csrf

                <x-simple-button>
                    {{ __('Sign out') }}
                </x-simple-button>
            </form>
        </div>
    </div>
</x-app-layout>
