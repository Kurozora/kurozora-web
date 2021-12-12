<div {{ $attributes->merge(['class' => 'flex flex-nowrap justify-between mb-5']) }}>
    <p class="text-xl font-semibold">{{ $title }}</p>

    {{ $action ?? '' }}
</div>
