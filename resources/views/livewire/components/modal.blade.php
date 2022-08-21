<x-dialog-modal wire:model="showPopup">
    @if($showVideo)
        <x-slot:title>
            {{ __(':x — Episode :y', ['x' => $episode->title, 'y' => $episode->number_total]) }}
        </x-slot:title>
        <x-slot:content>
            <iframe
                class="w-full aspect-video lazyload"
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
        </x-slot:content>
        <x-slot:footer>
            <x-button wire:click="$toggle('showPopup')">{{ __('Close') }}</x-button>
        </x-slot:footer>
    @else
        <x-slot:title>
            {{ $popupData['title'] }}
        </x-slot:title>
        <x-slot:content>
            <p>{{ $popupData['message'] }}</p>
        </x-slot:content>
        <x-slot:footer>
            <x-button wire:click="$toggle('showPopup')">{{ __('Ok') }}</x-button>
        </x-slot:footer>
    @endif
</x-dialog-modal>
