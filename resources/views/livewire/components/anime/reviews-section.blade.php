<div wire:init="loadReviews">
    <div class="flex justify-center">
        <x-spinner />
    </div>

    <x-rows.rating-lockup :ratings="$this->reviews" />
</div>
