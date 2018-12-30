@extends('website.layouts.landing')

@section('content')
    <h1 class="kurozora-title">{{ $animeData['title'] }}</h1>
    <h2 class="kurozora-under-title">{{ $animeData['episode_count'] }} episodes</h2>

    @if($animeData['poster'])
        <div class="anime-poster" style="background-image: url('{{ $animeData['poster'] }}')"></div>
    @endif

    <a href="{{ ios_app_url('anime/' . $animeData['id']) }}" class="kurozora-btn">
        Open in Kurozora App
    </a>
@endsection