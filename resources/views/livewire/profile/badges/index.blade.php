<div>
    <style>
        ul#badgesList > li:nth-child(even) {
            flex-direction: row-reverse;
        }
    </style>

    <ul id="badgesList" class="m-0 space-y-4">
        @foreach($this->badges as $badge)
            @php
                $borderColor = strtolower($badge->text_color) == '#ffffffff' ? '#ffffff80' : $badge->text_color;
            @endphp
            <li class="relative flex space-x-2 pt-4 pr-4 pb-4 pl-4 rounded-lg" style="background-color: {{ $badge->background_color }};">
                <picture class="relative w-16 h-16 aspect-square rounded-full overflow-hidden">
                    <img class="w-full" src="{{ $badge->getFirstMediaFullUrl(\App\Enums\MediaCollection::Symbol()) }}" alt="{{ $badge->name }} Badge Image" width="{{ $badge->getFirstMedia(\App\Enums\MediaCollection::Symbol)?->custom_properties['width'] ?? 96 }}" height="{{ $badge->getFirstMedia(\App\Enums\MediaCollection::Symbol)?->custom_properties['height'] ?? 96 }}">

                    <div class="absolute top-0 left-0 h-full w-full border-2 rounded-full" style="border-color: {{ $borderColor }};"></div>
                </picture>

                <div class="flex-1" style="color: {{ $badge->text_color }};">
                    <p class="font-semibold">{{ $badge->name }}</p>
                    <p class="text-sm">{{ $badge->description }}</p>
                </div>

                <div class="absolute top-0 right-0 w-full h-full border-2 rounded-lg" style="border-color: {{ $borderColor }};"></div>
            </li>
        @endforeach
    </ul>

    <div class="mt-4">
        {{ $this->badges->links() }}
    </div>
</div>
