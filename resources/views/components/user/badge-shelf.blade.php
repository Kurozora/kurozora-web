@props(['user'])

<div class="relative" x-data="badgeShelf()">
    <div class="overflow-hidden">
        <div class="flex gap-1 pl-1 pr-1 pt-1 pb-1 rounded overflow-x-scroll no-scrollbar">
            @if ($user->is_verified)
                <span
                    class="block cursor-pointer"
                    style="min-width: 18px; max-width: 18px;"
                    title="{{ __('This account is verified because it’s notable in animators, voice actors, entertainment studios, or another designated category.') }}"
                    x-on:click="updateTooltip($el)"
                >
                    @svg('badges-checkmark_seal_variable', 'text-orange-500 fill-current', ['width' => '100%'])
                </span>
            @endif

            @if ($user->is_staff)
                <span
                    class="block cursor-pointer"
                    style="min-width: 18px; max-width: 18px;"
                    title="{{ __('This account is a staff member.') }}"
                    x-on:click="updateTooltip($el)"
                >
                    @svg('badges-sakura_shield_variable', 'text-pink-400 fill-current', ['width' => '100%'])
                </span>
            @endif

            @if ($user->is_developer)
                <span
                    class="block cursor-pointer"
                    style="min-width: 18px; max-width: 18px;"
                    title="{{ __('This account is an active developer.') }}"
                    x-on:click="updateTooltip($el)"
                >
                    @svg('badges-hammer_app_variable', 'text-green-500 fill-current', ['width' => '100%'])
                </span>
            @endif

            @if ($user->is_early_supporter)
                <span
                    class="block cursor-pointer"
                    style="min-width: 18px; max-width: 18px;"
                    title="{{ __('This account is an early supporter of :x.', ['x' => config('app.name')]) }}"
                    x-on:click="updateTooltip($el)"
                >
                    @svg('badges-bird_triangle_variable', 'text-sky-500 fill-current', ['width' => '100%'])
                </span>
            @endif

            @if ($user->is_pro)
                <span
                    class="block cursor-pointer"
                    style="min-width: 18px; max-width: 18px;"
                    title="{{ __('This account is a Pro user.') }}"
                    x-on:click="updateTooltip($el)"
                >
                    @svg('badges-rocket_circle_variable', 'text-violet-500 fill-current', ['width' => '100%'])
                </span>
            @endif

            @if ($user->is_subscribed)
                <span
                    class="block cursor-pointer"
                    style="min-width: 18px; max-width: 18px;"
                    title="{{ __('This account is a :x+ subscriber since :y.', ['x' => config('app.name'), 'y' => $user->subscribed_at?->format('d F, Y')]) }}"
                    x-on:click="updateTooltip($el)"
                >
                    <x-picture>
                        @php ($subscribedMonths = (int) $user->created_at?->diffInMonths(now()))

                        @if ($subscribedMonths >= 24)
                            <img src="{{ asset('images/static/badges/24_months.webp') }}" alt="{{ __(':x+ 24 months', ['x' => config('app.name')]) }}">
                        @elseif ($subscribedMonths >= 18)
                            <img src="{{ asset('images/static/badges/18_months.webp') }}" alt="{{ __(':x+ 18 months', ['x' => config('app.name')]) }}">
                        @elseif ($subscribedMonths >= 15)
                            <img src="{{ asset('images/static/badges/15_months.webp') }}" alt="{{ __(':x+ 15 months', ['x' => config('app.name')]) }}">
                        @elseif ($subscribedMonths >= 12)
                            <img src="{{ asset('images/static/badges/12_months.webp') }}" alt="{{ __(':x+ 12 months', ['x' => config('app.name')]) }}">
                        @elseif ($subscribedMonths >= 9)
                            <img src="{{ asset('images/static/badges/9_months.webp') }}" alt="{{ __(':x+ 9 months', ['x' => config('app.name')]) }}">
                        @elseif ($subscribedMonths >= 6)
                            <img src="{{ asset('images/static/badges/6_months.webp') }}" alt="{{ __(':x+ 6 months', ['x' => config('app.name')]) }}">
                        @elseif ($subscribedMonths >= 3)
                            <img src="{{ asset('images/static/badges/3_months.webp') }}" alt="{{ __(':x+ 3 months', ['x' => config('app.name')]) }}">
                        @elseif ($subscribedMonths >= 2)
                            <img src="{{ asset('images/static/badges/2_months.webp') }}" alt="{{ __(':x+ 2 months', ['x' => config('app.name')]) }}">
                        @else
                            <img src="{{ asset('images/static/badges/1_month.webp') }}" alt="{{ __(':x+ 1 month', ['x' => config('app.name')]) }}">
                        @endif
                    </x-picture>
                </span>
            @endif
        </div>
    </div>

    <x-tooltip x-ref="badgeTooltip" x-show="tooltipOpen">
        <div x-html="icon" style="max-width: 48px;"></div>
        <p class="text-xs" x-text="text"></p>
    </x-tooltip>
</div>
