@props(['primary', 'secondary'])

<div class="flex flex-col gap-2 pt-6 pb-6 text-center max-w-2xl mx-auto">
    <p class="text-xl font-bold">{{ $primary }}</p>
    <p class="text-secondary">{!! nl2br(e($secondary)) !!}</p>
</div>
