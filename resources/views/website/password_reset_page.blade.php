@extends('website.layouts.landing')

@section('content')
    @if ($success)
        <h1 class="kurozora-title">Your password has been reset.</h1>
        <h2 class="kurozora-under-title">We have sent you an email with your new password.</h2>
    @else
        <h1 class="kurozora-title">Oops! We were unable to reset your password.</h1>
    @endif
@endsection