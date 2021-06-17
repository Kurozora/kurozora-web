<div {{ $attributes->merge(['class' => 'flex flex-col justify-between p-4 min-h-[150px] bg-gray-500 bg-opacity-20 rounded-lg']) }}>
    <div>
        <div class="flex flex-wrap w-full">
            <div class="bg-gray-500 h-[22px] w-[22px]" style="-webkit-mask: url('{{ $icon ?? asset('images/static/icon/logo.png') }}'); mask: url('{{ $icon ?? asset('images/static/icon/logo.png') }}');"></div>
            <p class="ml-1 text-gray-500">{{ $title }}</p>
        </div>
        <div class="mt-2">
            <p class="font-semibold text-2xl">{{ $information ?? '' }}</p>
        </div>
    </div>
    <div>
        {{ $slot }}
    </div>
    <div>
        {{ $footer ?? '' }}
    </div>
</div>
