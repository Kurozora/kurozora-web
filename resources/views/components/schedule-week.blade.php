@props(['releaseDate'])

@php
    $releaseDay = $releaseDate->copy()->startOfDay();
    $today = now()->inUserTimezone()->startOfDay();
    $weekEndingOnRelease = $releaseDay->copy()->subDays(6);
    $firstDay = $today->greaterThan($weekEndingOnRelease) ? $today : $weekEndingOnRelease;
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-row justify-between mt-2']) }}>
    @for ($offset = 0; $offset < 7; $offset++)
        @php
            $date = $firstDay->copy()->addDays($offset);
            $isToday = $date->isSameDay($today);
            $isRelease = $date->isSameDay($releaseDay);
        @endphp

        <div class="flex flex-col items-center">
            <p class="text-xs text-secondary">{{ narrow_weekday($date) }}</p>

            <div class="flex items-center justify-center w-7 h-7 mt-1 rounded-full {{ $isToday ? 'border border-primary' : '' }}">
                <div class="flex items-center justify-center w-6 h-6 rounded-full {{ $isRelease ? 'bg-tint' : '' }}">
                    <p class="text-xs {{ $isRelease ? 'btn-text-tinted font-bold' : '' }}">{{ $date->day }}</p>
                </div>
            </div>
        </div>
    @endfor
</div>
