<div wire:init="loadSection">
    @if ($this->reviews->count())
        <x-rows.review-lockup :reviews="$this->reviews" />
    @elseif (!$readyToLoad)
        <section>
            <div class="flex gap-4 justify-between snap-x overflow-x-scroll no-scrollbar">
                <div class="bg-gray-200 w-64 md:w-80 flex-grow" style="height: 168px;"></div>
                <div class="bg-gray-200 w-64 md:w-80 flex-grow" style="height: 168px;"></div>
                <div class="bg-gray-200 w-64 md:w-80 flex-grow" style="height: 168px;"></div>
                <div class="bg-gray-200 w-64 md:w-80 flex-grow" style="height: 168px;"></div>
                <div class="bg-gray-200 w-64 md:w-80 flex-grow" style="height: 168px;"></div>
                <div class="bg-gray-200 w-64 md:w-80 flex-grow" style="height: 168px;"></div>
                <div class="bg-gray-200 w-64 md:w-80 flex-grow" style="height: 168px;"></div>
                <div class="bg-gray-200 w-64 md:w-80 flex-grow" style="height: 168px;"></div>
                <div class="bg-gray-200 w-64 md:w-80 flex-grow" style="height: 168px;"></div>
                <div class="w-64 md:w-80 flex-grow"></div>
                <div class="w-64 md:w-80 flex-grow"></div>
            </div>
        </section>
    @endif
</div>
