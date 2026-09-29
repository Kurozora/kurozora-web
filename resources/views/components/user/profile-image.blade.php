<div
    class="relative flex"
    @if ($onProfile)
        data-section
        data-section-refresh-on="refresh-profile-image"
        data-section-url="{{ $refreshUrl }}"
    @endif
>
    @if ($onProfile)
        <span
            x-data="presenceStatus({{ Js::from(['id' => $user->id, 'status' => $activityStatus->value, 'labels' => $statusLabels]) }})"
            title="{{ $activityStatus->description }}"
            x-bind:title="label"
        >
            <svg class="relative overflow-hidden" width="96" height="96" viewBox="0 0 96 96">
                <foreignObject height="96" width="96" mask="url(#svg-mask-avatar-status-round-80)">
                    <x-picture
                        class="w-full h-full"
                        style="background-color: {{ $profileImage?->custom_properties['background_color'] ?? 'var(--bg-secondary-color)' }}"
                    >
                        <img
                            class="w-full h-full object-cover"
                            src="{{ $profileImage?->getFullUrl() ?? $user->getFallbackMediaUrl(\App\Enums\MediaCollection::Profile) }}"
                            alt="{{ $user->username }} Profile Image"
                            width="{{ $profileImage?->custom_properties['width'] ?? 96 }}"
                            height="{{ $profileImage?->custom_properties['height'] ?? 96 }}"
                        >
                    </x-picture>

                    <div class="absolute top-0 left-0 h-full w-full"></div>
                </foreignObject>

                <rect
                    width="18"
                    height="18"
                    x="72"
                    y="72"
                    mask="url(#svg-mask-status-online)"
                    @class([
                        'fill-current',
                        'text-green-500' => $activityStatus->is(\App\Enums\UserActivityStatus::Online),
                        'text-yellow-500' => $activityStatus->is(\App\Enums\UserActivityStatus::SeenRecently),
                        'text-red-500' => $activityStatus->is(\App\Enums\UserActivityStatus::Offline),
                    ])
                    x-bind:class="{
                        'text-green-500': status === {{ \App\Enums\UserActivityStatus::Online }},
                        'text-yellow-500': status === {{ \App\Enums\UserActivityStatus::SeenRecently }},
                        'text-red-500': status === {{ \App\Enums\UserActivityStatus::Offline }},
                    }"
                ></rect>
            </svg>
        </span>
    @else
        <x-picture
            class="w-full h-full rounded-full"
            style="background-color: {{ $profileImage?->custom_properties['background_color'] ?? 'var(--bg-secondary-color)' }}"
        >
            <img
                class="w-16 h-16 object-cover rounded-full sm:w-24 sm:h-24"
                src="{{ $profileImage?->getFullUrl() ?? $user->getFallbackMediaUrl(\App\Enums\MediaCollection::Profile) }}"
                alt="{{ $user->username }} Profile Image"
                width="{{ $profileImage?->custom_properties['width'] ?? 96 }}"
                height="{{ $profileImage?->custom_properties['height'] ?? 96 }}"
            >

            <div class="absolute top-0 left-0 h-full w-full"></div>
        </x-picture>
    @endif
</div>
