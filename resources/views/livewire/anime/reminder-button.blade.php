@php
    $classes = $isReminded
               ? 'drop-shadow inline-flex items-center max-w-[150px] px-4 py-2 bg-white text-gray-500 font-semibold text-xs uppercase tracking-widest border-0 rounded-full shadow-md focus:ring-0 disabled:backdrop-filter disabled:bg-white/80 disabled:backdrop-blur disabled:text-gray-400 disabled:opacity-100 disabled:cursor-default'
               : 'inline-flex items-center max-w-[150px] px-4 py-2 bg-orange-500 text-white font-semibold text-xs uppercase tracking-widest border-0 rounded-full shadow-md hover:bg-orange-400 active:bg-orange-600 focus:ring-0 disabled:backdrop-filter disabled:bg-white/80 disabled:backdrop-blur disabled:text-gray-400 disabled:cursor-default';
@endphp

<button class="{{ $classes }}" wire:click="remindAnime" {{ $disabled ? 'disabled' : '' }}>
    {{ __('Remind Me') }}
</button>

