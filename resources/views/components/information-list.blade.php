@props(['title', 'information' => null, 'footer' => null, 'icon' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col justify-between pt-4 pr-4 pb-4 pl-4 min-h-[150px] bg-gray-500 bg-opacity-20 rounded-lg']) }}>
    <div>
        <div class="flex flex-wrap w-full">
            @if(!empty($icon))
                <div class="bg-gray-500 h-[22px] w-[22px] mr-1" style="-webkit-mask: url('{{ $icon }}'); mask: url('{{ $icon }}');"></div>
            @endif
            <p class="text-gray-500">{{ $title }}</p>
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
