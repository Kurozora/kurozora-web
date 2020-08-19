@extends('website.layouts.landing')

@section('content')
    <div class="text-center my-12">
        <h1 class="text-white">You’re not authorized to view this page.</h1>
        <h5 class="text-white">{{ $exception->getMessage() }}</h5>
        <br>
        <a href="{{ url('/') }}" class="">Go back to home page?</a>
    </div>
@endsection
