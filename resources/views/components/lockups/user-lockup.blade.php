@props(['user'])

@php
    $followersCount = $user->followers()->count();
@endphp

<div {{ $attributes->merge(['class' => 'relative w-full']) }}>
    <div class="flex flex-nowrap justify-between">
        <div class="flex">
            <picture class="relative w-16 h-16 overflow-hidden">
                <img
                    class="w-full bg-white border-2 border-black/5 rounded-full lazyload"
                    data-sizes="auto"
                    data-src="{{ $user->profile_image_url }}"
                    alt="{{ $user->username }} Profile Image"
                    title="{{ $user->username }}"
                    width="{{ $user->profile_image?->custom_properties['width'] ?? 96 }}"
                    height="{{ $user->profile_image?->custom_properties['height'] ?? 96 }}"
                >

                <div class="absolute top-0 left-0 h-full w-full rounded-full"></div>
            </picture>

            <div class="px-2">
                <p class="leading-tight line-clamp-2">{{ $user->username }}</p>
                <p class="text-xs leading-tight text-black/60">
                    @auth
                        @if ($followersCount === 0)
                            @if ($user->id === Auth::user()->id)
                                {{ __('You, followed by you!') }}
                            @else
                                {{ __('Be the first to follow!') }}
                            @endif
                        @elseif ($followersCount === 1)
                            @if ($user->id == Auth::user()->id)
                                {{ __('Followed by you... and one fan!') }}
                            @else
                                {{
                                   $user->followers()->where('user_id', Auth::user()->id)->exists() ?
                                   __('Followed by you.') :
                                   __('Followed by one user.')
                                }}
                            @endif
                        @elseif ($followersCount >= 2 && $followersCount <= 999)
                            @if ($user->id == Auth::user()->id)
                                {{ __('Followed by you and :x fans.', ['x' => $followersCount]) }}
                            @else
                                {{
                                    $user->followers()->where('user_id', Auth::user()->id)->exists() ?
                                    __('Followed by you and :x users.', ['x' => $followersCount - 1]) :
                                    __('Followed by :x users.', ['x' => $followersCount])
                                }}
                            @endif
                        @else
                            @if ($user->id == Auth::user()->id)
                                {{ __('Followed by :x fans.', ['x' => number_shorten($followersCount)]) }}
                            @else
                                {{
                                    $user->followers()->where('user_id', Auth::user()->id)->exists() ?
                                    __('Followed by you and :x users.', ['x' => number_shorten($followersCount - 1)]) :
                                    __('Followed by :x users.', ['x' => number_shorten($followersCount)])
                                }}
                            @endif
                        @endif
                    @else
                        {{ __('Be the first to follow!') }}
                    @endauth
                </p>
            </div>
        </div>

        <a class="absolute w-full h-full" href="{{ route('profile.details', $user) }}"></a>

        <div class="flex flex-row flex-nowrap justify-between z-10 whitespace-nowrap">
            @auth
                @if ($user->id != Auth::user()->id)
                    <livewire:components.follow-button :user='$user' wire:key="{{ uniqid(more_entropy: true) }}" />
                @endif
            @else
                <livewire:components.follow-button :user='$user' wire:key="{{ uniqid(more_entropy: true) }}" />
            @endauth
        </div>
    </div>
</div>