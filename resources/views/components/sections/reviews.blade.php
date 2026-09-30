<div data-section data-section-url="{{ $refreshUrl }}" data-section-refresh-on="review-submitted review-deleted review-elevated">
    @if ($editorial)
        <div class="pb-4 pl-4 pr-4 xl:safe-area-inset">
            <x-lockups.editorial-lockup :editorial="$editorial" />
        </div>
    @endif

    @if ($reviews->count())
        <x-rows.review-lockup :reviews="$reviews" :review-box-id="$reviewBoxId" />
    @endif
</div>
