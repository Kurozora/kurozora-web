<div {{ $attributes->merge(['class' => 'flex flex-nowrap justify-between mb-5']) }}>
    <div>
        <p class="text-xl font-bold">{{ $title }}</p>
        <p class="text-gray-500 font-semibold">{{ $description ?? '' }}</p>
    </div>

    {{ $action ?? '' }}
</div>
