<div class="flex gap-2 justify-center items-center">
    @if (!$disabled)
        {{-- Remove rating button --}}
        @if ($reaction !== null)
            <button
                    type="button"
                    wire:click="rate(-2)"
                    class="p-2 text-secondary hover:text-primary transition-colors"
                    title="{{ __('Remove rating') }}"
            >
                <svg class="w-4 h-4 fill-current" viewBox="0 0 23 22" xmlns="http://www.w3.org/2000/svg">
                    <path d="M5.01531446,20.8270441 C5.40168729,21.1285963 5.89169759,21.0249395 6.47596587,20.6008944 L11.4610053,16.9351426 L16.4554627,20.6008944 C17.0397117,21.0249395 17.5203039,21.1285963 17.9160948,20.8270441 C18.3024677,20.5349292 18.3873076,20.0543216 18.1516819,19.3664062 L16.1821647,13.5049895 L21.2142752,9.8863628 C21.7986593,9.47171643 22.0341113,9.03823391 21.8833834,8.56705977 C21.7326555,8.11474108 21.2897356,7.88857209 20.5640804,7.89799017 L14.3917069,7.93570109 L12.5164284,2.04600505 C12.2902594,1.34866192 11.9510155,1 11.4610053,1 C10.9803937,1 10.6411499,1.34866192 10.4149809,2.04600505 L8.53970243,7.93570109 L2.36730962,7.89799017 C1.64169296,7.88857209 1.19879235,8.11474108 1.04802589,8.56705977 C0.887822052,9.03823391 1.1328272,9.47171643 1.71709549,9.8863628 L6.74924456,13.5049895 L4.77972739,19.3664062 C4.54414032,20.0543216 4.62896093,20.5349292 5.01531446,20.8270441 Z" />
                </svg>
            </button>
        @endif
    @endif

    {{-- Sad reaction --}}
    <button
            type="button"
            wire:click="rate(-1)"
            @class([
                'text-3xl p-2 rounded-full transition-all duration-200',
                'hover:scale-125 hover:bg-red-500/10 cursor-pointer' => !$disabled,
                'opacity-100 scale-110 bg-red-500/20' => $reaction === -1,
                'opacity-50 hover:opacity-100' => $reaction !== null && $reaction !== -1 && !$disabled,
                'cursor-not-allowed' => $disabled,
            ])
            {{ $disabled ? 'disabled' : '' }}
            title="{{ __('Disliked it') }}"
    >
        {{ \App\Enums\RatingStyle::getQuickReactionEmoji(-1) }}
    </button>

    {{-- Neutral reaction --}}
    <button
            type="button"
            wire:click="rate(0)"
            @class([
                'text-3xl p-2 rounded-full transition-all duration-200',
                'hover:scale-125 hover:bg-yellow-500/10 cursor-pointer' => !$disabled,
                'opacity-100 scale-110 bg-yellow-500/20' => $reaction === 0,
                'opacity-50 hover:opacity-100' => $reaction !== null && $reaction !== 0 && !$disabled,
                'cursor-not-allowed' => $disabled,
            ])
            {{ $disabled ? 'disabled' : '' }}
            title="{{ __('It was okay') }}"
    >
        {{ \App\Enums\RatingStyle::getQuickReactionEmoji(0) }}
    </button>

    {{-- Happy reaction --}}
    <button
            type="button"
            wire:click="rate(1)"
            @class([
                'text-3xl p-2 rounded-full transition-all duration-200',
                'hover:scale-125 hover:bg-green-500/10 cursor-pointer' => !$disabled,
                'opacity-100 scale-110 bg-green-500/20' => $reaction === 1,
                'opacity-50 hover:opacity-100' => $reaction !== null && $reaction !== 1 && !$disabled,
                'cursor-not-allowed' => $disabled,
            ])
            {{ $disabled ? 'disabled' : '' }}
            title="{{ __('Loved it') }}"
    >
        {{ \App\Enums\RatingStyle::getQuickReactionEmoji(1) }}
    </button>
</div>
