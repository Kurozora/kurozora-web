<div>
    @if ($this->editorial)
        <div class="pb-4 pl-4 pr-4 xl:safe-area-inset">
            <x-lockups.editorial-lockup :editorial="$this->editorial" />
        </div>
    @endif

    @if ($this->reviews->count())
        <x-rows.review-lockup :reviews="$this->reviews" :vote-overrides="$voteOverrides" :review-box-id="$reviewBoxID" />
    @endif

    @include('livewire.components.reviews.report-form')
</div>
