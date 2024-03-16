<div>
    <x-rows.media-rating-lockup :media-ratings="$this->mediaRatings" :is-row="false" />

    <div class="mt-4">
        {{ $this->mediaRatings->links() }}
    </div>
</div>
