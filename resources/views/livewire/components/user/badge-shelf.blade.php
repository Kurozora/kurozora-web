<div
    class="relative"
    x-data="{
        icon: null,
        text: null,
        tooltipOpen: false,
        updateTooltip($el) {
            let isOpen = $el.innerHTML !== this.icon ? true : !this.tooltipOpen
            this.icon = $el.innerHTML
            this.text = $el.attributes['title'].value

            this.calculateTooltipPosition(this.tooltipOpen && !isOpen)
            this.tooltipOpen = isOpen
        },
        calculateTooltipPosition(resetPosition) {
            const tooltip = $refs.badgeTooltip
            tooltip.style.top = ''
            tooltip.style.right = ''
            tooltip.style.bottom = ''
            tooltip.style.left = ''
            tooltip.style.transform = ''

            if (resetPosition) {
                return
            }

            const tooltipRect = tooltip.getBoundingClientRect()
            const viewportWidth = window.innerWidth || document.documentElement.clientWidth
            const viewportHeight = window.innerHeight || document.documentElement.clientHeight
            const tooltipWidth = tooltipRect.width
            const tooltipHeight = tooltipRect.height
            const space = {
                top: tooltipRect.top,
                right: viewportWidth - tooltipRect.right,
                bottom: viewportHeight - tooltipRect.bottom,
                left: tooltipRect.left
            }

            let [top, bottom, left, right, transform] = ['', '', '', '', '']

            // Determine the direction with the most available space
            if (space.top >= tooltipHeight && space.top >= space.bottom && space.top >= space.left && space.top >= space.right) {
                bottom = '100%'
                left = '50%'
                transform = 'translateX(-50%)'
            } else if (space.bottom >= tooltipHeight && space.bottom >= space.top && space.bottom >= space.left && space.bottom >= space.right) {
                top = '100%'
                left = '50%'
                transform = 'translateX(-50%)'
            } else if (space.left >= tooltipWidth && space.left >= space.top && space.left >= space.bottom && space.left >= space.right) {
                top = '50%;'
                right = '100%'
                transform = 'translateY(-50%)'
            } else if (space.right >= tooltipWidth && space.right >= space.top && space.right >= space.bottom && space.right >= space.left) {
                top = '50%'
                left = '100%'
                transform = 'translateY(-50%)'
            }

            tooltip.style.top = top
            tooltip.style.right = right
            tooltip.style.bottom = bottom
            tooltip.style.left = left
            tooltip.style.transform = transform
        }
    }"
>
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
                    title="{{ __('This account is an early supporter of Kurozora.') }}"
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
                    title="{{ __('This account is a Kurozora+ subscriber since :x.', ['x' => $user->subscribed_at?->format('d F, Y')]) }}"
                    x-on:click="updateTooltip($el)"
                >
                    <x-picture>
                        @php ($subscribedMonths = (int) $user->created_at?->diffInMonths(now()))

                        @if ($subscribedMonths >= 24)
                            <img src="{{ asset('images/static/badges/24_months.webp') }}" alt="{{ __('Kurozora+ 24 months') }}">
                        @elseif ($subscribedMonths >= 18)
                            <img src="{{ asset('images/static/badges/18_months.webp') }}" alt="{{ __('Kurozora+ 18 months') }}">
                        @elseif ($subscribedMonths >= 15)
                            <img src="{{ asset('images/static/badges/15_months.webp') }}" alt="{{ __('Kurozora+ 15 months') }}">
                        @elseif ($subscribedMonths >= 12)
                            <img src="{{ asset('images/static/badges/12_months.webp') }}" alt="{{ __('Kurozora+ 12 months') }}">
                        @elseif ($subscribedMonths >= 9)
                            <img src="{{ asset('images/static/badges/9_months.webp') }}" alt="{{ __('Kurozora+ 9 months') }}">
                        @elseif ($subscribedMonths >= 6)
                            <img src="{{ asset('images/static/badges/6_months.webp') }}" alt="{{ __('Kurozora+ 6 months') }}">
                        @elseif ($subscribedMonths >= 3)
                            <img src="{{ asset('images/static/badges/3_months.webp') }}" alt="{{ __('Kurozora+ 3 months') }}">
                        @elseif ($subscribedMonths >= 2)
                            <img src="{{ asset('images/static/badges/2_months.webp') }}" alt="{{ __('Kurozora+ 2 months') }}">
                        @else
                            <img src="{{ asset('images/static/badges/1_month.webp') }}" alt="{{ __('Kurozora+ 1 month') }}">
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
