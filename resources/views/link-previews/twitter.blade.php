<div class="mt-4 border border-black/5 rounded-lg overflow-hidden">
    <a class="flex flex-col gap-2 pt-3 pb-3 pr-3 pl-3 bg-secondary no-external-icon" href="{{ $url }}" target="_blank"
       rel="noopener noreferrer">
        <div class="flex items-center gap-2">
            @if ($media_url)
                <img class="w-8 object-cover aspect-square rounded-full" src="{{ 'https://cdn.kurozora.app/178480/014c1439-cdc6-4f9d-8df7-a77512981820.webp?v=1681498696' }}"
                     alt="{{ $author }}">
            @endif

            <p class="font-bold text-sm">{{ $author }}</p>
        </div>

        <p>{{ $description }}</p>

        <p class="text-xs text-secondary">{{ __('View on Twitter') }}</p>
    </a>
</div>
