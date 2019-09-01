@extends('website.layouts.landing')

@section('content')
    <h1 class="kurozora-title">Privacy Policy</h1>
    <p class="kurozora-paragraph">
        {!! $policyText !!}
    </p>

    <a href="{{ url('/') }}" class="back-safe-link">Take me back</a>
@endsection