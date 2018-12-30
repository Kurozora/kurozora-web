@extends('website.layouts.landing')

@section('content')
    <h1 class="kurozora-title">{{ $threadData['title'] }}</h1>
    <h2 class="kurozora-under-title">thread posted {{ $threadData['date'] }}</h2>

    <a href="{{ ios_app_url('thread/' . $threadData['id']) }}" class="kurozora-btn">
        Open in Kurozora App
    </a>
@endsection