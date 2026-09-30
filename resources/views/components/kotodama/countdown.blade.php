<div data-section data-section-url="{{ $refreshUrl }}" data-section-refresh-on="kotodama-finished">
    @if($game && $game->isFinished() && $nextPuzzleAt)
        <div class="flex h-10 items-center justify-center pl-4 pr-4 xl:safe-area-inset-scroll">
            <p
                class="text-sm text-primary text-center"
                data-template="{{ __('Next Kotodama in :time') }}"
                x-data="kotodamaCountdown({ unlockAt: {{ $nextPuzzleAt }} })"
                x-init="start()"
                x-text="text"
            ></p>
        </div>
    @endif
</div>
