<div
    x-data="{
        preferredVideoSource: $persist(@entangle('preferredVideoSource').live)
    }"
    wire:init="loadSection"
>
    @if ($this->videos->count())
        <x-dropdown align="right" width="48">
            <x-slot:trigger>
                <x-circle-button
                    title="{{ __('Source') }}"
                >
                    @svg('list_and_film', 'fill-current', ['width' => '28'])
                </x-circle-button>
            </x-slot:trigger>

            <x-slot:content>
                @foreach ($this->videos as $video)
                    <button
                        :class="{'bg-secondary text-primary hover:bg-tertiary focus:bg-secondary': preferredVideoSource !== '{{ $video->source->key }}', 'bg-tint btn-text-tinted': preferredVideoSource === '{{ $video->source->key }}'}"
                        class="block w-full pl-4 pr-4 pt-2 pb-2 text-xs text-center font-semibold"
                        wire:click="selectPreferredSource('{{ $video->source->key }}')"
                    >
                        {{ $video->source->description }}
                    </button>
                @endforeach
            </x-slot:content>
        </x-dropdown>
    @endif
</div>
