<div wire:init="loadReviews">
    <div class="flex justify-center">
        <x-spinner />
    </div>

    <x-rows.review-lockup :reviews="$this->reviews" />
</div>
