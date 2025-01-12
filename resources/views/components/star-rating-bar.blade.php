@props(['mediaStat'])

@php
    $ratingTotal = max($mediaStat->rating_count, 1);
    $rating1 = $mediaStat->rating_9 + $mediaStat->rating_10;
    $rating2 = $mediaStat->rating_7 + $mediaStat->rating_8;
    $rating3 = $mediaStat->rating_5 + $mediaStat->rating_6;
    $rating4 = $mediaStat->rating_3 + $mediaStat->rating_4;
    $rating5 = $mediaStat->rating_1 + $mediaStat->rating_2;

    $rating1Percentage = number_format(100 / $ratingTotal * $rating1);
    $rating2Percentage = number_format(100 / $ratingTotal * $rating2);
    $rating3Percentage = number_format(100 / $ratingTotal * $rating3);
    $rating4Percentage = number_format(100 / $ratingTotal * $rating4);
    $rating5Percentage = number_format(100 / $ratingTotal * $rating5);
@endphp

<figure class="flex flex-col items-end gap-1">
    <div class="flex gap-2 items-center w-full sm:w-auto" title="{{ __(':x% rated 5 stars', ['x' => $rating1Percentage]) }}">
        <div class="flex gap-1">
            @svg('star_fill', 'fill-current', ['width' => 10])
            @svg('star_fill', 'fill-current', ['width' => 10])
            @svg('star_fill', 'fill-current', ['width' => 10])
            @svg('star_fill', 'fill-current', ['width' => 10])
            @svg('star_fill', 'fill-current', ['width' => 10])
        </div>

        <div class="w-full h-1 bg-secondary rounded-full sm:w-64">
            <div class="h-1 bg-inverse-primary rounded-full" style="width: {{ $rating1Percentage }}%;"></div>
        </div>
    </div>

    <div class="flex gap-2 items-center w-full sm:w-auto" title="{{ __(':x% rated 4 stars', ['x' => $rating2Percentage]) }}">
        <div class="flex gap-1">

            @svg('star_fill', 'fill-current text-transparent', ['width' => 10])
            @svg('star_fill', 'fill-current', ['width' => 10])
            @svg('star_fill', 'fill-current', ['width' => 10])
            @svg('star_fill', 'fill-current', ['width' => 10])
            @svg('star_fill', 'fill-current', ['width' => 10])
        </div>

        <div class="w-full h-1 bg-secondary rounded-full sm:w-64">
            <div class="h-1 bg-inverse-primary rounded-full" style="width: {{ $rating2Percentage }}%;"></div>
        </div>
    </div>

    <div class="flex gap-2 items-center w-full sm:w-auto" title="{{ __(':x% rated 3 stars', ['x' => $rating3Percentage]) }}">
        <div class="flex gap-1">

            @svg('star_fill', 'fill-current text-transparent', ['width' => 10])
            @svg('star_fill', 'fill-current text-transparent', ['width' => 10])
            @svg('star_fill', 'fill-current', ['width' => 10])
            @svg('star_fill', 'fill-current', ['width' => 10])
            @svg('star_fill', 'fill-current', ['width' => 10])
        </div>

        <div class="w-full h-1 bg-secondary rounded-full sm:w-64">
            <div class="h-1 bg-inverse-primary rounded-full" style="width: {{ $rating3Percentage }}%;"></div>
        </div>
    </div>

    <div class="flex gap-2 items-center w-full sm:w-auto" title="{{ __(':x% rated 2 stars', ['x' => $rating4Percentage]) }}">
        <div class="flex gap-1">
            @svg('star_fill', 'fill-current text-transparent', ['width' => 10])
            @svg('star_fill', 'fill-current text-transparent', ['width' => 10])
            @svg('star_fill', 'fill-current text-transparent', ['width' => 10])
            @svg('star_fill', 'fill-current', ['width' => 10])
            @svg('star_fill', 'fill-current', ['width' => 10])
        </div>

        <div class="w-full h-1 bg-secondary rounded-full sm:w-64">
            <div class="h-1 bg-inverse-primary rounded-full" style="width: {{ $rating4Percentage }}%;"></div>
        </div>
    </div>

    <div class="flex gap-2 items-center w-full sm:w-auto" title="{{ __(':x% rated 1 star', ['x' => $rating5Percentage]) }}">
        <div class="flex gap-1">
            @svg('star_fill', 'fill-current text-transparent', ['width' => 10])
            @svg('star_fill', 'fill-current text-transparent', ['width' => 10])
            @svg('star_fill', 'fill-current text-transparent', ['width' => 10])
            @svg('star_fill', 'fill-current text-transparent', ['width' => 10])
            @svg('star_fill', 'fill-current', ['width' => 10])
        </div>

        <div class="w-full h-1 bg-secondary rounded-full sm:w-64">
            <div class="h-1 bg-inverse-primary rounded-full" style="width: {{ $rating5Percentage }}%;"></div>
        </div>
    </div>
</figure>
