@props(['playable' => true, 'goto' => false, 'links' => [], 'title' => '', 'link' => ''])

@if ($playable)
    <x-menu.item data-music-play>
        <span data-music-icon="play" class="inline-flex items-center gap-2">
            <span class="shrink-0">@svg('play_fill', 'fill-current', ['width' => 12])</span>
            {{ __('Play') }}
        </span>
        <span data-music-icon="pause" class="hidden inline-flex items-center gap-2">
            <span class="shrink-0">@svg('pause_fill', 'fill-current', ['width' => 12])</span>
            {{ __('Pause') }}
        </span>
    </x-menu.item>

    <x-menu.item icon="plus" data-music-add class="hidden">{{ __('Add to Apple Music') }}</x-menu.item>
    <x-menu.item icon="checkmark" data-music-added class="hidden">{{ __('In Apple Music Library') }}</x-menu.item>

    <x-hr class="my-1" />
@endif

@if ($goto)
    <x-menu.item icon="music_note" data-music-goto>{{ __('Go to Song') }}</x-menu.item>
@endif

<div data-music-services class="hidden">
    <x-menu.submenu icon="arrow_up_forward" :label="__('View on')">
        <x-menu.item icon="music_smile_circle_fill" :href="$links['amazon'] ?? '#'" data-music-service="amazon" class="hidden">{{ __('View on Amazon Music') }}</x-menu.item>
        <x-menu.item icon="music_note_circle_fill" href="#" data-music-service="appleMusic" class="hidden">{{ __('View on Apple Music') }}</x-menu.item>
        <x-menu.item icon="music_waveform_circle_fill" :href="$links['deezer'] ?? '#'" data-music-service="deezer" class="hidden">{{ __('View on Deezer') }}</x-menu.item>
        <x-menu.item icon="wave_3_up_circle_fill" :href="$links['spotify'] ?? '#'" data-music-service="spotify" class="hidden">{{ __('View on Spotify') }}</x-menu.item>
        <x-menu.item icon="play_circle_circle_fill" :href="$links['youtube'] ?? '#'" data-music-service="youtube" class="hidden">{{ __('View on Youtube Music') }}</x-menu.item>
    </x-menu.submenu>

    <x-hr class="my-1" />
</div>

<x-menu.submenu icon="document_on_document_fill" :label="__('Copy')">
    <x-menu.item icon="document_on_document_fill" data-music-copy data-music-copy-title data-copy-value="{{ $title }}">{{ __('Copy Title') }}</x-menu.item>
    <x-menu.item icon="document_on_document_fill" data-music-copy data-music-copy-link data-copy-value="{{ $link }}">{{ __('Copy Link') }}</x-menu.item>
</x-menu.submenu>

@isset($share)
    {{ $share }}
@else
    <x-menu.item icon="square_and_arrow_up_fill" data-music-share>{{ __('Share') }}</x-menu.item>
@endisset
