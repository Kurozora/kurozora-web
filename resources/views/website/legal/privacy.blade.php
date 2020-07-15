@extends('website.layouts.landing')

@section('content')
    <div class="container mx-auto px-12">
        <div class="text-center mt-16">
            <h1>Privacy Policy</h1>
        </div>
        {!! $policyText !!}

        <a href="{{ url('/') }}" class="back-safe-link">Take me back</a>
    </div>
@endsection
