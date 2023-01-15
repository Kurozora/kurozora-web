<div class="relative flex">
    @if ($onProfile)
        <span title="{{ $user->getActivityStatus()->description }}">
            <svg class="relative overflow-hidden" width="96" height="96" viewBox="0 0 96 96">
                <foreignObject height="96" width="96" mask="url(#svg-mask-avatar-status-round-80)">
                    <img class="bg-white" src="{{ $user->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) }}" alt="{{ $user->username }} Profile Image" width="{{ $user->getFirstMedia(\App\Enums\MediaCollection::Profile)?->custom_properties['width'] ?? 96 }}" height="{{ $user->getFirstMedia(\App\Enums\MediaCollection::Profile)?->custom_properties['height'] ?? 96 }}">

                    <div class="absolute top-0 left-0 h-full w-full"></div>
                </foreignObject>

                @switch ($user->getActivityStatus()->value)
                    @case(\App\Enums\UserActivityStatus::SeenRecently)
                        <rect width="18" height="18" x="72" y="72" mask="url(#svg-mask-status-online)" class="text-yellow-500 fill-current"></rect>
                        @break
                    @case(\App\Enums\UserActivityStatus::Online)
                        <rect width="18" height="18" x="72" y="72" mask="url(#svg-mask-status-online)" class="text-green-500 fill-current"></rect>
                        @break
                    @case(\App\Enums\UserActivityStatus::Offline)
                        <rect width="18" height="18" x="72" y="72" mask="url(#svg-mask-status-online)" class="text-red-500 fill-current"></rect>
                @endswitch
            </svg>
        </span>
    @else
        <picture class="relative w-full overflow-hidden">
            <img class="w-16 h-16 bg-white border-2 border-black/5 rounded-full sm:w-24 sm:h-24" src="{{ $user->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) }}" alt="{{ $user->username }} Profile Image" width="{{ $user->getFirstMedia(\App\Enums\MediaCollection::Profile)?->custom_properties['width'] ?? 96 }}" height="{{ $user->getFirstMedia(\App\Enums\MediaCollection::Profile)?->custom_properties['height'] ?? 96 }}">

            <div class="absolute top-0 left-0 h-full w-full"></div>
        </picture>
    @endif
</div>
