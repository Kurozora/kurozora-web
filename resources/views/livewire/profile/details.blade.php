<div class="flex flex-col w-full h-full items-center justify-center">
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

    <div class="profile-image" style="background-image: url('{{ $user->profile_image_url }}')"></div>

    <h1 class="font-bold mt-6">{{ $user->username }}</h1>
    <h2 class="mb-2">{{ $user->getFollowerCount() }} {{ __('followers') }}</h2>

    <x-link-button href="{{ ios_app_url('profile/' . $user->id) }}" class="rounded-full">
        {{ __('Open in Kurozora App') }}
    </x-link-button>
</div>
