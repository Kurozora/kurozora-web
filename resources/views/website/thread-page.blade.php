@extends('website.layouts.landing')

@section('content')
    <div class="flex flex-col items-center justify-center h-screen">
        <h1 class="text-white">{{ $threadData['title'] }}</h1>
        <h2 class="text-white">Posted {{ $threadData['date'] }}</h2>

        <a href="{{ ios_app_url('thread/' . $threadData['id']) }}" class="kurozora-btn">
            Open in Kurozora App
        </a>
    </div>
@endsection
