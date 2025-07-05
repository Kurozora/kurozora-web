@props(['user'])

<a
    {{ $attributes->merge(['class' => 'flex aspect-square rounded-full']) }}
    href="{{ $user ? route('profile.details', $user) : route('sign-in') }}"
    wire:navigate
>
    <picture
        class="relative bg-primary border-2 border-black/5 rounded-full overflow-hidden"
        style="background-color: {{ $user?->getFirstMedia(\App\Enums\MediaCollection::Profile)?->custom_properties['background_color'] ?? 'var(--bg-secondary-color)' }};"
    >
        <img
            class="w-full h-full object-cover"
            src="{{ $user?->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) ?? asset('images/static/placeholders/user_profile.webp') }}"
            alt="{{ $user?->username ?? __('Guest') }} {{ __('Profile') }}"
            title="{{ $user?->username ?? __('Guest') }}"
            width="{{ $user?->getFirstMedia(\App\Enums\MediaCollection::Profile)?->custom_properties['width'] ?? 96 }}"
            height="{{ $user?->getFirstMedia(\App\Enums\MediaCollection::Profile)?->custom_properties['height'] ?? 96 }}"
        >

        <div class="absolute top-0 left-0 h-full w-full"></div>
    </picture>
</a>
