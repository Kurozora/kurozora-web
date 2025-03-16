@props(['title', 'information' => null, 'footer' => null, 'icon' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col justify-between pt-4 pr-4 pb-4 pl-4 min-h-[150px] bg-secondary rounded-lg']) }}>
    <div>
        <div class="flex flex-wrap w-full">
            @if (!empty($icon))
                <div
                    class="aspect-square bg-inverse-secondary mr-1"
                    style="width: 22px; mask: url('{{ $icon }}');"
                ></div>
            @endif
            <p class="text-secondary">{{ $title }}</p>
        </div>
        <div class="mt-2">
            <p class="font-semibold text-2xl">{{ $information }}</p>
        </div>
    </div>
    <div>
        {{ $slot }}
    </div>
    <div>
        {{ $footer }}
    </div>
</div>
