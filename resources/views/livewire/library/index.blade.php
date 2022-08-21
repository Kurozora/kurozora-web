<main>
    <x-slot:title>
        {{ __('Library') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Join Kurozora and build your own anime and manga library for free. Keep track of the anime you love, and the ones you will love next.') }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Library') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Join Kurozora and build your own anime and manga library for free. Keep track of the anime you love, and the ones you will love next.') }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
    </x-slot:meta>

    <div
        class="max-w-7xl mx-auto px-4 py-6 sm:px-6"
        x-data="{
            selectedStatus: @entangle('status')
        }"
    >
        <div class="flex flex-nowrap gap-x-4 justify-between text-center whitespace-nowrap overflow-x-scroll no-scrollbar">
            @foreach(\App\Enums\UserLibraryStatus::asSelectArray() as $key => $value)
                <button
                    class="px-4 pb-2 border-b-2 hover:border-orange-500"
                    :class="{'border-orange-500': '{{ strtolower($status) }}' === '{{ strtolower($value) }}', 'border-gray-300': '{{ strtolower($status) }}' !== '{{ strtolower($value) }}'}"
                    wire:click="$set('status', '{{ strtolower($value) }}')"
                    data-toggle="tab"
                >{{ __($value) }}</button>
            @endforeach
        </div>

        <section class="mt-8">
            @foreach(\App\Enums\UserLibraryStatus::asSelectArray() as $key => $value)
                <livewire:library.tab id="{{ $value }}" :status="$value" wire:key="{{ md5($value) }}" />
            @endforeach
        </section>
    </div>
</main>
