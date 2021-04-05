<div class="container mx-auto px-4">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Themes') }}
        </h2>
    </x-slot>

    <div class="flex flex-row flex-wrap">

        @foreach($themes as $theme)
            <x-theme-thumbnail :theme="$theme" />
        @endforeach
    </div>

    {{ $themes->links() }}
</div>
