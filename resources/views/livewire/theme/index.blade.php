<div>
    <x-slot name="title">
        {{ __('Themes') }}
    </x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight self-center">
            {{ __('Themes') }}
        </h2>

        <div class="flex flex-wrap justify-end items-center w-full">
            <x-link-button href="{{ route('themes.create') }}">{{ __('Create') }}</x-link-button>
        </div>
    </x-slot>

    <div class="flex flex-row flex-wrap">

        @foreach($themes as $theme)
            <x-theme-thumbnail :theme="$theme" />
        @endforeach
    </div>

    {{ $themes->links() }}
</div>
