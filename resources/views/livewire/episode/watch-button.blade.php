@switch ($buttonStyle)
    @case('circle')
        @php
            $class = match ($hasWatched) {
                true => 'inline-flex items-center w-10 pt-2 pr-2 pb-2 pl-2 bg-orange-500 border border-transparent rounded-full font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-400 active:bg-orange-600 focus:outline-none active:border-orange-600 active:ring-orange disabled:bg-gray-100 disabled:border-gray-200 disabled:text-gray-300 disabled:cursor-default transition ease-in-out duration-150',
                default => 'inline-flex items-center w-10 pl-2 pr-2 pt-2 pb-2 bg-white/60 backdrop-blur border border-transparent rounded-full font-semibold text-xs text-gray-500 uppercase tracking-widest shadow-md hover:opacity-75 active:opacity-50 focus:outline-none disabled:bg-gray-100 disabled:text-gray-300 disabled:cursor-default disabled:opacity-100 transition ease-in-out duration-150'
            };
        @endphp

        <button
            class="{{ $class }}"
            title="{{ $hasWatched ? __('Mark as Unwatched') : __('Mark as Watched') }}"
            wire:click="updateWatchStatus"
        >
            @svg('checkmark', 'fill-current', ['width' => '28'])
        </button>
    @break
    @default
        <x-tinted-pill-button
            :color="$hasWatched ? 'orange' : ''"
            :title="$hasWatched ? __('Mark as Unwatched') : __('Mark as Watched')"
            wire:click="updateWatchStatus"
        >
            @svg('checkmark', 'fill-current', ['width' => 14])
            {{ $hasWatched ? __('Watched') : __('Watch') }}
        </x-tinted-pill-button>
@endswitch

