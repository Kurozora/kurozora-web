@props(['isRow' => true])

@php
    $class = $isRow ? 'lockup-profile' : 'lockup-profile-grid';
@endphp

<div {{ $attributes->merge(['class' => $class]) }}>
    <p class="w-full aspect-square bg-secondary rounded-full"></p>

    <div class="flex justify-center mt-2">
        <p class="bg-secondary rounded-md" style="width: 80%; height: 18px"></p>
    </div>
</div>
