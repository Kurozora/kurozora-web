@props(['achievement'])

<div
    class="flex flex-col items-center justify-center border-2 border-white rounded-xl shadow-lg overflow-hidden"
    style="min-height: 392px; background: linear-gradient(0deg, #d7d7d7 0%, #fdfdfd 100%);"
>
    <div class="w-24 mt-8 mr-4 ml-4 aspect-square">
        <div class="flex items-center justify-center w-28 mt-2 bg-gray-200 aspect-square rounded-full overflow-hidden">
            @if ($achievement->is_achieved)
                <picture class="relative">
                    <img
                        class="w-full aspect-square lazyload"
                        data-sizes="auto"
                        data-src="{{ $achievement->getFirstMediaFullUrl(\App\Enums\MediaCollection::Symbol()) }}"
                        alt="{{ $achievement->name }} Symbol"
                        title="{{ $achievement->name }}"
                    >

                    <div
                        class="absolute top-0 left-0 h-full w-full border-2 border-black/20 rounded-full"
                        style="box-shadow: inset 0 0 10px 0 rgba(0,0,0, 0.5);"
                    ></div>
                </picture>
            @else
                <div class="relative flex items-center justify-center w-full text-gray-400 aspect-square">
                    @svg('lock_fill', 'fill-current', ['width' => '32'])

                    <div
                        class="absolute top-0 left-0 h-full w-full rounded-full"
                        style="box-shadow: inset 0 0 10px 0 #8c8c8c;"
                    ></div>
                </div>
            @endif
        </div>
    </div>

    <div class="flex flex-col justify-between h-full mb-8">
        <div class="flex flex-col flex-grow pr-4 pl-4 pt-2 pb-2 text-center">
            <p class="font-semibold text-2xl">{{ $achievement->name }}</p>
            <p class="text-gray-400 text-sm">{{ $achievement->description }}</p>
        </div>

        <p class="text-gray-400 text-sm text-center font-semibold">
            {{ $achievement->achieved_at?->format('M d, Y') }}
        </p>
    </div>
</div>