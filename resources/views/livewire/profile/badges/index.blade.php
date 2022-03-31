<div>
    <style>
        ul#badgesList > li:nth-child(even) {
            flex-direction: row-reverse;
        }
    </style>

    <ul id="badgesList" class="m-0 space-y-4">
        @foreach($this->badges as $badge)
            <li class="flex space-x-2 p-4 rounded-lg" style="background-color: {{ $badge->background_color }};">
                <picture class="relative w-16 h-16 aspect-ratio-1-1 overflow-hidden">
                    <img class="w-full bg-white border-2 border-black/5 rounded-full" src="{{ $badge->symbol_image_url }}" alt="{{ $badge->name }} Badge Image" width="{{ $badge->symbol_image?->custom_properties['width'] ?? 96 }}" height="{{ $badge->symbol_image?->custom_properties['height'] ?? 96 }}">

                    <div class="absolute top-0 left-0 h-full w-full"></div>
                </picture>

                <div class="flex-1" style="color: {{ $badge->text_color }};">
                    <p class="font-semibold">{{ $badge->name }}</p>
                    <p class="text-sm">{{ $badge->description }}</p>
                </div>
            </li>
        @endforeach
    </ul>

    <div class="mt-4">
        {{ $this->badges->links() }}
    </div>
</div>
