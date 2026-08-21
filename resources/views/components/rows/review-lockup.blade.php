@props(['reviews' => [], 'isRow' => true, 'safeAreaInsetEnabled' => true, 'voteOverrides' => [], 'reviewBoxId' => null])

@php
    $class = $isRow ? 'snap-mandatory snap-x overflow-x-scroll no-scrollbar' : 'flex-wrap';

    if ($isRow && $safeAreaInsetEnabled) {
        $class .= ' xl:safe-area-inset-scroll';
    }

    // The rows of a paginated set live on its own collection.
    $allReviews = $reviews instanceof \Illuminate\Contracts\Pagination\CursorPaginator
        || $reviews instanceof \Illuminate\Contracts\Pagination\Paginator
            ? $reviews->getCollection()
            : collect($reviews);

    // The horizontal row shows too few reviews to be worth collapsing.
    $collapsesLowEffort = !$isRow;
    $shownReviews = $collapsesLowEffort ? $allReviews->reject->is_low_effort : $allReviews;
    $lowEffortReviews = $collapsesLowEffort ? $allReviews->filter->is_low_effort : collect();
@endphp

<div {{ $attributes->merge(['class' => 'flex gap-4 justify-between pl-4 pr-4 ' . $class]) }}>
    @foreach ($shownReviews as $review)
        <x-lockups.review-lockup :review="$review" :is-row="$isRow" :vote-overrides="$voteOverrides" :review-box-id="$reviewBoxId" />
    @endforeach

    @if ($lowEffortReviews->isNotEmpty())
        <div class="w-full" x-data="{ isExpanded: false }">
            <button
                type="button"
                class="flex items-center gap-1 pl-2 pr-2 pt-1 pb-1 text-sm text-secondary rounded-md bg-secondary"
                x-on:click="isExpanded = !isExpanded"
            >
                <span x-show="!isExpanded">{{ __('Show short reviews') }}</span>
                <span x-show="isExpanded" x-cloak>{{ __('Hide short reviews') }}</span>
            </button>

            <div class="flex flex-wrap gap-4 justify-between mt-4" x-show="isExpanded" x-cloak>
                @foreach ($lowEffortReviews as $review)
                    <x-lockups.review-lockup :review="$review" :is-row="$isRow" :vote-overrides="$voteOverrides" :review-box-id="$reviewBoxId" />
                @endforeach

                <div class="w-64 sm:w-96 flex-grow"></div>
                <div class="w-64 sm:w-96 flex-grow"></div>
            </div>
        </div>
    @endif

    <div class="w-64 sm:w-96 flex-grow"></div>
    <div class="w-64 sm:w-96 flex-grow"></div>
</div>
