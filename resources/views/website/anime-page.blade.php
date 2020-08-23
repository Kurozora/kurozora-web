@extends('website.layouts.landing')

@section('content')
    <div class="flex flex-col w-full h-full items-center justify-center">
        <div class="anime-poster" style="background-image: url('{{ $animeData['poster'] ?? asset('img/static/placeholders/anime_poster.jpg') }}')"></div>

        <h1 class="text-white">{{ $animeData['title'] }}</h1>

        @if($animeData['episode_count'])
            <h2 class="text-white">{{ $animeData['episode_count'] }} episodes</h2>
        @endif

        <a href="{{ ios_app_url('anime/' . $animeData['id']) }}" class="k-button">
            Open in Kurozora App
        </a>
    </div>
@endsection
