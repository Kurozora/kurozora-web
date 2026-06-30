<div class="flex flex-col items-center gap-4 pt-6 pb-6 text-center max-w-2xl mx-auto">
    {{ $slot }}

    <p class="text-xs text-secondary">{{ __('Payment will be charged to your Apple ID account at the confirmation of purchase. Subscription automatically renews unless it is canceled at least 24 hours before the end of the current period. Your account will be charged for renewal within 24 hours prior to the end of the current period. You can manage and cancel your subscriptions by going to your account settings on the App Store after purchase.') }}</p>

    <p class="flex items-center gap-1 text-sm">
        <x-simple-link href="{{ route('legal.terms-of-use') }}" wire:navigate>{{ __('Terms of Use') }}</x-simple-link>
        {{ __('and') }}
        <x-simple-link href="{{ route('legal.privacy-policy') }}" wire:navigate>{{ __('Privacy Policy') }}</x-simple-link>
    </p>
</div>
