<div {{ $attributes->merge(['class' => 'flex gap-2 items-center justify-between mb-5 pl-4 pr-4']) }}>
    <div>
        <h2 class="text-xl font-bold">{{ $title }}</h2>
        <p class="text-secondary">{{ $description ?? '' }}</p>
    </div>

    <div class="flex flex-wrap gap-2 justify-end">
        {{ $action ?? '' }}
    </div>
</div>
