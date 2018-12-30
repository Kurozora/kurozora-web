@extends('website.layouts.landing')

@section('content')
    <h1 class="kurozora-title">{{ $userData['username'] }}</h1>
    <h2 class="kurozora-under-title">{{ $userData['followers'] }} followers</h2>

    @if($userData['avatar'])
        <div class="user-avatar-preview" style="background-image: url('{{ $userData['avatar'] }}')"></div>
    @endif

    <a href="{{ ios_app_url('profile/' . $userData['id']) }}" class="kurozora-btn">
        Open in Kurozora App
    </a>
@endsection