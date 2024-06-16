<div {{ $attributes->merge(['class' => 'flex gap-2 items-center justify-between mb-5']) }}>
    <div>
        <h2 class="text-xl font-bold">{{ $title }}</h2>
        <p class="text-gray-500">{{ $description ?? '' }}</p>
    </div>

    <div class="flex flex-wrap gap-2 justify-end">
        {{ $action ?? '' }}
    </div>
</div>
