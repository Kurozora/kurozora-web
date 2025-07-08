<div class="mt-4">
    @if (!empty($embed_html))
        {!! $embed_html !!}
    @else
        @switch($type->value)
            @case(\App\Enums\LinkPreviewType::VIDEO)
                <x-link-preview.video
                    :title="$title"
                    :media-url="$media_url"
                    :description="$description"
                    :author="$author"
                />
                @break
            @default
                <a class="flex bg-secondary no-external-icon rounded-md overflow-hidden" href="{{ $url }}" target="_blank"
                   rel="noopener noreferrer">
                    @if ($media_url)
                        <img class="w-28 object-cover aspect-square" src="{{ $media_url }}"
                             alt="{{ $author }}">
                    @endif

                    <div class="flex flex-col gap-1 pt-3 pb-3 pr-3 pl-3">
                        <p class="text-xs">{{ $author }}</p>
                        <p class="font-bold">{{ $title }}</p>
                        <p class="text-xs line-clamp-2">{{ $description }}</p><br>
                    </div>
                </a>
        @endswitch
    @endif
</div>
