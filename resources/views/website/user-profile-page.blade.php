@extends('website.layouts.landing')

@section('content')
    <div class="flex flex-col items-center justify-center h-screen">
        <div class="user-avatar" style="background-image: url('{{ $userData['avatar'] ?? '' }}')"></div>

        <h1 class="text-white">{{ $userData['username'] }}</h1>
        <h2 class="text-white">{{ $userData['followers'] }} followers</h2>

        <a href="{{ ios_app_url('profile/' . $userData['id']) }}" class="kurozora-btn">
            Open in Kurozora App
        </a>
    </div>
@endsection
