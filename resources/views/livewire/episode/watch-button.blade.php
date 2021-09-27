@php
    $class = match ($hasWatched) {
        true => 'inline-flex items-center w-10 p-2 bg-orange-500 border border-transparent rounded-full font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-400 active:bg-orange-600 focus:outline-none active:border-orange-600 active:ring-orange disabled:bg-gray-100 disabled:border-gray-200 disabled:text-gray-300 disabled:cursor-default transition ease-in-out duration-150',
        default => 'inline-flex items-center w-10 px-2 py-2 bg-white/60 backdrop-blur border border-transparent rounded-full font-semibold text-xs text-gray-500 uppercase tracking-widest shadow-md hover:opacity-75 active:opacity-50 focus:outline-none disabled:bg-gray-100 disabled:text-gray-300 disabled:cursor-default disabled:opacity-100 transition ease-in-out duration-150'
    };
@endphp

<button
    class="{{ $class }}"
    wire:click="updateWatchStatus"
>
    @svg('checkmark', 'fill-current', ['width' => '44'])
</button>
