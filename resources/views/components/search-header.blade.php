<div {{ $attributes->merge(['class' => 'flex gap-2 justify-between items-baseline mb-5']) }}>
    <div>
        <p class="mt-2 text-sm text-primary font-semibold uppercase">{{ $title }}</p>
    </div>

    <div class="flex flex-wrap gap-2 justify-end">
        {{ $action ?? '' }}
    </div>
</div>
