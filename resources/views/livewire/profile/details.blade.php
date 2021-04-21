<div class="flex flex-col w-full h-full items-center justify-center">
    <x-slot name="title">
        {{ $user->username }}
    </x-slot>

    <x-slot name="meta">
        <meta property="og:title" content="{{ $page['title'] }}" />
        <meta property="og:image" content="{{ $page['image'] }}" />
        <meta property="og:type" content="{{ $page['type'] }}" />
    </x-slot>

    <div class="profile-image" style="background-image: url('{{ $user->profile_image_url }}')"></div>

    <h1 class="font-bold mt-6">{{ $user->username }}</h1>
    <h2 class="mb-2">{{ $user->getFollowerCount() }} {{ __('followers') }}</h2>

    <x-link-button href="{{ ios_app_url('profile/' . $user->id) }}" class="rounded-full">
        {{ __('Open in Kurozora App') }}
    </x-link-button>
</div>
