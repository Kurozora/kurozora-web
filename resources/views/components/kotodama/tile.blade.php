@props(['letter', 'state' => null])

<div @class([
    'relative flex items-center justify-center w-12 h-12 border-2 rounded text-lg font-bold uppercase',
    'kotodama-tile-hit' => $state === 'hit',
    'kotodama-tile-present' => $state === 'present',
    'kotodama-tile-miss' => $state === 'miss',
    'bg-primary border-primary text-primary' => $state === null,
])>
    {{ $letter }}
</div>
