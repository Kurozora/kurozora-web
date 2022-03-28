<x-dialog-modal maxWidth="md" wire:model="showPopup">
    @if($showVideo)
        <x-slot:title>
            {{ __(':x — Episode :y', ['x' => $episode->title, 'y' => $episode->number_total]) }}
        </x-slot>
        <x-slot:content>
            <iframe
                class="w-full aspect-ratio-16-9 lazyload"
                type="text/html"
                scrolling="no"
                frameborder="no"
                allowfullscreen="allowfullscreen"
                mozallowfullscreen="mozallowfullscreen"
                msallowfullscreen="msallowfullscreen"
                oallowfullscreen="oallowfullscreen"
                webkitallowfullscreen="webkitallowfullscreen"
                allow="autoplay; fullscreen;"
                data-size="auto"
                data-src="{{ $episode->video_url }}"
            >
            </iframe>
        </x-slot>
        <x-slot:footer>
            <x-button wire:click="$toggle('showPopup')">{{ __('Close') }}</x-button>
        </x-slot>
    @else
        <x-slot:title>
            {{ $popupData['title'] }}
        </x-slot>
        <x-slot:content>
            <p>{{ $popupData['message'] }}</p>
            </x-slot>
        <x-slot:footer>
            <x-button wire:click="$toggle('showPopup')">{{ __('Ok') }}</x-button>
        </x-slot>
    @endif
</x-dialog-modal>
