@props(['theme'])

<div class="max-w-lg sm:max-w-lg md:max-w-xs lg:max-w-sm rounded overflow-hidden shadow-lg">
    <img class="w-full" src="https://bulma.io/images/placeholders/1280x960.png" alt="{{ $theme->name }}">

    <div class="px-6 py-4">
        <div class="font-bold text-xl mb-2">{{ $theme->name }}</div>
        <p class="text-gray-700 text-base"></p>
    </div>

    <div class="px-6 pt-4 pb-2">
        <span class="inline-block bg-gray-200 rounded-full px-3 py-1 text-sm font-semibold text-gray-700 mr-2 mb-2">{{ __('Free') }}</span>
    </div>
</div>
