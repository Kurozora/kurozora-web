@extends('website.layouts.landing')

@section('content')
    @if ($success)
        <h1 class="kurozora-title">You have successfully confirmed your email address.</h1>
    @else
        <h1 class="kurozora-title">Oops! We were unable to confirm your email address.</h1>
    @endif
@endsection