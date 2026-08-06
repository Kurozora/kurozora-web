<nav class="flex flex-wrap gap-4 pt-2 pl-4 pr-4 xl:safe-area-inset-scroll">
    <x-section-nav-link :href="route('kotodama.unlimited')">
        {{ __('Unlimited') }}
    </x-section-nav-link>

    <x-section-nav-link :href="route('kotodama.leaderboards')">
        {{ __('Leaderboards') }}
    </x-section-nav-link>

    @auth
        <x-section-nav-link :href="route('kotodama.stats')">
            {{ __('My Stats') }}
        </x-section-nav-link>

        @if(auth()->user()->is_pro || auth()->user()->is_subscribed)
            <x-section-nav-link :href="route('kotodama.archive')">
                {{ __('Archive') }}
            </x-section-nav-link>
        @endif
    @endauth
</nav>
