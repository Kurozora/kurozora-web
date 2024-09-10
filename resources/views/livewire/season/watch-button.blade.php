<div>
    <div class="inline-block relative">
        <x-tinted-pill-button
            :color="'orange'"
            :title="$this->hasWatched ? __('Mark all episodes as unwatched') : __('Mark all episodes as watched')"
            wire:click="updateWatchStatus"
            wire:loading.attr="disabled"
        >
            @if ($this->hasWatched)
                @svg('checkmark', 'fill-current', ['width' => 12])
                {{ __('Watched') }}
            @else
                {{ __('Mark All Watched') }}
            @endif
        </x-tinted-pill-button>
    </div>
</div>
