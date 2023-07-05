@props(['rating', 'isRow' => true])

@php
    $class = $isRow ? 'pb-2 shrink-0 snap-normal snap-center' : '';
@endphp

<div {{ $attributes->merge(['class' => 'relative flex-grow w-64 sm:w-96 ' . $class]) }}>
    <div
        class="relative flex flex-row gap-2 pr-2 pl-2 pt-2 pb-2 h-full bg-gray-100 rounded-xl"
        x-data="{}"
        wire:key="{{ uniqid($rating->id, true) }}"
    >
        <x-profile-image-view class="w-16 h-16" :user="$rating->user" />

        <div class="flex flex-col items-baseline w-full overflow-hidden">
            <div class="flex gap-2 justify-between w-full">
                <div class="flex gap-1 overflow-hidden">
                    <a class="inline-flex items-center text-sm font-semibold whitespace-nowrap overflow-hidden" href="{{ route('profile.details', $rating->user) }}">{{ $rating->user->username }}</a>

                    <livewire:components.user.badge-shelf :user="$rating->user" wire:key="{{ uniqid('badges-', true) }}" />
                </div>

                <p class="text-sm text-gray-500 whitespace-nowrap" title="{{ $rating->created_at->toFormattedDateString() }}">{{ $rating->created_at->toFormattedDateString() }}</p>
            </div>

            <div>
                <livewire:anime.star-rating :rating="$rating->rating" :star-size="'sm'" :disabled="true" wire:key="{{ uniqid('rating-', true) }}" />
            </div>

            <div class="mt-2 w-full">
                <p class="line-clamp-5" style="word-break: break-word;">{!! nl2br($rating->description) !!}</p>
            </div>

{{--            <div class="flex gap-2 justify-between w-full">--}}
{{--                <div class="flex justify-between">--}}
{{--                    <x-square-button :class="true ? 'text-red-500' : ''">--}}
{{--                        @svg('heart_fill', 'fill-current', ['width' => '18'])--}}
{{--                        <p class="ml-2 mr-2">{{ '1.9K' }}</p>--}}
{{--                    </x-square-button>--}}
{{--                </div>--}}

{{--                <div>--}}
{{--                    <x-square-button>--}}
{{--                        @svg('ellipsis', 'fill-current', ['width' => '18'])--}}
{{--                    </x-square-button>--}}
{{--                </div>--}}
{{--            </div>--}}
        </div>
    </div>
</div>
