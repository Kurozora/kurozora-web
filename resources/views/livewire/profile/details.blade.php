<main>
    <x-slot name="title">
        {{ $user->username }}
    </x-slot>

    <x-slot name="meta">
        <meta property="og:title" content="{{ $user->username . __(' on Kurozora') }}" />
        <meta property="og:description" content="{{ $user->biography }}" />
        <meta property="og:image" content="{{ $user->profile_image_url }}" />
        <meta property="og:type" content="profile" />
        <meta property="og:profile:username" content="{{ $user->username }}" />
    </x-slot>

    <x-slot name="appArgument">
        users/{{ $user->id }}
    </x-slot>

{{--    <div class="profile-image" style="background-image: url('{{ $user->profile_image_url }}')"></div>--}}

{{--    <h1 class="font-bold mt-6">{{ $user->username }}</h1>--}}
{{--    <h2 class="mb-2">{{ $user->getFollowerCount() }} {{ __('followers') }}</h2>--}}

{{--    <x-link-button href="{{ ios_app_url('profile/' . $user->id) }}" class="rounded-full">--}}
{{--        {{ __('Open in Kurozora App') }}--}}
{{--    </x-link-button>--}}
    <div class="">
        <section>
            @if ($user->banner_image)
                <img src="{{ $user->banner_image_url }}" alt="{{ $user->username }} Banner Image">
            @else
                <div class="inline-block w-full h-80 bg-orange-500"></div>
            @endif
        </section>

        <section class="max-w-7xl mx-auto px-4 py-6 sm:px-6">
            <div class="flex items-end justify-between -mt-20">
                <div class="flex items-end">
                    <img class="w-24 h-24 bg-white border-2 border-black/5 rounded-full" src="{{ $user->profile_image_url }}" alt="{{ $user->username }} Profile Image" width="{{ $user->profile_image?->custom_properties['width'] ?? 96 }}" height="{{ $user->profile_image?->custom_properties['height'] ?? 96 }}">

                    <span class="flex items-baseline">
                        <p class="ml-2 text-xl font-bold">{{ $user->username }}</p>
                        @switch ($user->getActivityStatus()->value)
                            @case(\App\Enums\UserActivityStatus::Online || \App\Enums\UserActivityStatus::SeenRecently)
                                <span class="block ml-1 w-2 h-2 bg-green-500 rounded-full"></span>
                                @break
                            @case(\App\Enums\UserActivityStatus::Offline)
                                <span class="block ml-1 w-2 h-2 bg-red-500 rounded-full"></span>
                        @endswitch
                    </span>
                </div>

                @auth
                    @if ($user->id == Auth::user()->id)
                        <x-button>{{ __('Edit') }}</x-button>
                    @else
                        <x-button>
                            @if ($user->followers()->where('user_id', Auth::user()->id)->exists())
                                {{ __('✓︎ Following') }}
                            @else
                                {{ __('+ Follow') }}
                            @endif
                        </x-button>
                    @endif
                @endif
            </div>

            <x-textarea class="mt-2" :readonly="true">{{ $user->biography }}</x-textarea>
        </section>
    </div>
</main>
