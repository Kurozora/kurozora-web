@php
    $avatar = $user->getFirstMediaUrl('avatar');
@endphp

<div class="flex flex-col w-full h-full items-center justify-center">
    <x-slot name="open-graph">
        <meta property="og:title" content="{{ $page['title'] }}" />
        <meta property="og:image" content="{{ $page['image'] }}" />
        <meta property="og:type" content="{{ $page['type'] }}" />
    </x-slot>

    <div class="user-avatar" style="background-image: url('{{ !empty($avatar) ? $avatar : asset('images/static/placeholders/user_profile.png') }}')"></div>

    <h1 class="text-white font-bold mt-6">{{ $user->username }}</h1>
    <h2 class="text-white mb-2">{{ $user->getFollowerCount() }} {{ __('followers') }}</h2>

    <x-link-button href="{{ ios_app_url('profile/' . $user->id) }}" class="rounded-full">
        {{ __('Open in Kurozora App') }}
    </x-link-button>
</div>
