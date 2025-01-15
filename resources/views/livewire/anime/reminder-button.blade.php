@php
    $classes = $isReminded
               ? 'drop-shadow inline-flex justify-center items-center pl-4 pr-4 pt-2 pb-2 bg-white text-gray-500 font-semibold text-xs uppercase tracking-widest border-0 rounded-full shadow-md focus:ring-0 disabled:backdrop-filter disabled:bg-white/80 disabled:backdrop-blur disabled:text-gray-400 disabled:opacity-100 disabled:cursor-default'
               : 'drop-shadow inline-flex justify-center items-center pl-4 pr-4 pt-2 pb-2 bg-tint text-white font-semibold text-xs uppercase tracking-widest border-0 rounded-full shadow-md hover:bg-orange-400 active:bg-orange-600 focus:ring-0 disabled:backdrop-filter disabled:bg-white/80 disabled:backdrop-blur disabled:text-gray-400 disabled:cursor-default';
@endphp

<button
    class="{{ $classes }}"
    style="min-width: 100px;"
    wire:click="remindAnime" {{ $isReminded ? 'disabled' : '' }}
>
    @if ($isReminded)
        @svg('checkmark', 'fill-current', ['width' => '16'])
    @else
        {{ __('Remind Me') }}
    @endif
</button>

