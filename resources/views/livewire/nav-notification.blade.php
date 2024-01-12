<div class="pt-2 pb-2">
    <p class="pr-2 mb-2 pl-2 text-lg font-semibold">{{ __('Notifications') }}</p>

    @if ($isNotificationOpen)
        <ul class="flex flex-col gap-2 m-0">
            @foreach ($this->notifications as $key => $notification)
                <li>
                    @switch($notification->type)
{{--                        @case(\App\Notifications\NewSession::class)--}}
{{--                            {{ $notification->data['ipAddress'] }}--}}
{{--                        @break--}}
{{--                        @case(\App\Notifications\SubscriptionStatus::class)--}}
{{--                            {{ $notification->data['subscriptionStatus'] }}--}}
{{--                        @break--}}
{{--                        @case(\App\Notifications\LocalLibraryImportFinished::class)--}}
{{--                            {{ $notification->data['behavior'] }}--}}
{{--                        @break--}}
{{--                        @case(\App\Notifications\LibraryImportFinished::class)--}}
{{--                            {{ $notification->data['library'] }}--}}
{{--                        @break--}}
{{--                        @case(\App\Notifications\NewFeedMessageReply::class)--}}
{{--                            {{ $notification->data['username'] }}--}}
{{--                        @break--}}
{{--                        @case(\App\Notifications\NewFollower::class)--}}
{{--                            {{ $notification->data['username'] }}--}}
{{--                        @break--}}
                        @default
                            <p class="pr-2 pl-2 text-sm">{{ $notification->description }}</p>
                    @endswitch

                    @if ($key != $this->notifications->count() - 1)
                        <x-hr class="mt-2" />
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
</div>
