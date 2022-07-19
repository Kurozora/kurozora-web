<div {{ $attributes->merge(['class' => 'flex gap-2 justify-between mb-5']) }}>
    <div>
        <p class="text-xl font-bold">{{ $title }}</p>
        <p class="text-gray-500 font-semibold">{{ $description ?? '' }}</p>
    </div>

    <div class="flex flex-wrap gap-2 justify-end">
        {{ $action ?? '' }}
    </div>
</div>
