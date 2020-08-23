@extends('website.layouts.landing')

@section('content')
    <div class="text-center my-12">
        <h1 class="text-white">The page you’re looking for can’t be found.</h1>
        <br>
        <a href="{{ url('/') }}" class="">Go back to home page?</a>
    </div>
@endsection
