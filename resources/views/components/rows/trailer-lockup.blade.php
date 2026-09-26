@props(['videos' => [], 'isRow' => true, 'safeAreaInsetEnabled' => true, 'marksLibrary' => false])

@if (!empty($videos))
    <x-rows.container lockup="trailer" :is-row="$isRow" :safe-area-inset-enabled="$safeAreaInsetEnabled" {{ $attributes }}>
        @foreach ($videos as $video)
            <x-lockups.trailer-lockup :video="$video" :is-row="$isRow" :in-library="$marksLibrary && $video->videoable?->library?->isNotEmpty()" />
        @endforeach
    </x-rows.container>
@endif
