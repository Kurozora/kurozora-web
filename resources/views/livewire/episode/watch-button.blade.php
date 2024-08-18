<div class="inline-block relative">
    <x-tinted-pill-button
        :color="'orange'"
        :title="$hasWatched ? __('Mark as Unwatched') : __('Mark as Watched')"
        wire:click="updateWatchStatus"
    >
        @if ($hasWatched)
            @svg('checkmark', 'fill-current', ['width' => 12])
            {{ __('Watched') }}
        @else
            {{ __('Mark as Watched') }}
        @endif
    </x-tinted-pill-button>

{{--    @if ($hasWatched)--}}
{{--        <x-circle-button--}}
{{--            :color="'orange'"--}}
{{--            wire:click="updateReWatchCount"--}}
{{--        >--}}
{{--            <div class="relative">--}}
{{--                @svg('arrow_clockwise', 'absolute fill-current', ['width' => 44])--}}
{{--                1--}}
{{--            </div>--}}
{{--        </x-circle-button>--}}
{{--    @endif--}}
</div>
