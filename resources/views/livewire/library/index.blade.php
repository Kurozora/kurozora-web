<main>
    <x-slot name="title">
        {{ __('Library') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Join Kurozora and build your own anime and manga library for free. Keep track of the anime you love, and the ones you will love next.') }}
    </x-slot>

    <x-slot name="meta">
        <meta property="og:title" content="{{ __('Library') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Join Kurozora and build your own anime and manga library for free. Keep track of the anime you love, and the ones you will love next.') }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
    </x-slot>

    <div
        class="max-w-7xl mx-auto px-4 py-6 sm:px-6"
        x-data="{ tab: window.location.hash ? window.location.hash : '#watching' }"
    >
        <div class="flex flex-nowrap gap-x-4 justify-between text-center whitespace-nowrap overflow-x-scroll no-scrollbar">
            <a
                class="px-4 pb-2 border-b-2 hover:border-green-500"
                :class="{'border-green-500': tab === '#watching', 'border-gray-300': tab !== '#watching'}"
                x-on:click="tab = '#watching'"
                href="#watching"
                data-toggle="tab"
            >{{ __('Watching') }}</a>

            <a
                class="px-4 pb-2 border-b-2 hover:border-orange-500"
                :class="{'border-orange-500': tab === '#planning', 'border-gray-300': tab !== '#planning'}"
                href="#planning"
                data-toggle="tab"
                x-on:click="tab = '#planning'"
            >{{ __('Planning') }}</a>

            <a
                class="px-4 pb-2 border-b-2 hover:border-blue-500"
                :class="{'border-blue-500': tab === '#completed', 'border-gray-300': tab !== '#completed'}"
                href="#completed"
                data-toggle="tab"
                x-on:click="tab = '#completed'"
            >{{ __('Completed') }}</a>

            <a
                class="px-4 pb-2 border-b-2 hover:border-red-500"
                :class="{'border-red-500': tab === '#onhold', 'border-gray-300': tab !== '#onhold'}"
                href="#onhold"
                data-toggle="tab"
                x-on:click="tab = '#onhold'"
            >{{ __('On-Hold') }}</a>

            <a
                class="px-4 pb-2 border-b-2 hover:border-gray-700"
                :class="{'border-gray-500': tab === '#dropped', 'border-gray-300': tab !== '#dropped'}"
                href="#dropped"
                data-toggle="tab"
                x-on:click="tab = '#dropped'"
            >{{ __('Dropped') }}</a>
        </div>

        <section class="mt-8">
            <livewire:library.tab id="watching" :user-library-status="\App\Enums\UserLibraryStatus::Watching()" wire:key="{{ md5('watching') }}" />

            <livewire:library.tab id="planning" :user-library-status="\App\Enums\UserLibraryStatus::Planning()" wire:key="{{ md5('planning') }}" />

            <livewire:library.tab id="completed" :user-library-status="\App\Enums\UserLibraryStatus::Completed()" wire:key="{{ md5('completed') }}" />

            <livewire:library.tab id="onhold" :user-library-status="\App\Enums\UserLibraryStatus::OnHold()" wire:key="{{ md5('onhold') }}" />

            <livewire:library.tab id="dropped" :user-library-status="\App\Enums\UserLibraryStatus::Dropped()" wire:key="{{ md5('dropped') }}" />
        </section>
    </div>
</main>
