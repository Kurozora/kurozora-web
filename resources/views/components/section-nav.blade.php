<div {{ $attributes->merge(['class' => 'flex flex-no-wrap justify-between mb-5']) }}>
    <p class="text-xl font-semibold">{{ $title }}</p>

    {{ $action ?? '' }}
</div>
