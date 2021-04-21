<div class="flex flex-col w-full h-full items-center justify-center">
    <x-slot name="title">
        {{ $thread->title }}
    </x-slot>

    <x-slot name="meta">
        <meta property="og:title" content="{{ $page['title'] }}" />
        <meta property="og:type" content="{{ $page['type'] }}" />
    </x-slot>

    <h1 class="font-bold">{{ $thread->title }}</h1>
    <h2 class="mb-2">{{ __('Posted') }} {{ $thread->created_at->diffForHumans() }}</h2>

    <x-link-button href="{{ ios_app_url('thread/' . $thread->id) }}" class="rounded-full">
        {{ __('Open in Kurozora App') }}
    </x-link-button>
</div>
