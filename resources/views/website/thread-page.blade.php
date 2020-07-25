@extends('website.layouts.landing')

@section('content')
    <div class="flex flex-col w-full h-full items-center justify-center">
        <h1 class="text-white">{{ $threadData['title'] }}</h1>
        <h2 class="text-white">Posted {{ $threadData['date'] }}</h2>

        <a href="{{ ios_app_url('thread/' . $threadData['id']) }}" class="k-button">
            Open in Kurozora App
        </a>
    </div>
@endsection
