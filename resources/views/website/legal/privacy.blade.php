@extends('website.layouts.app')

@section('content')
    <div class="container mx-auto px-12 mb-8">
        <div class="text-center mt-16">
            <h1>Privacy Policy</h1>
        </div>
        {!! $policyText !!}
        <div>
            <a href="{{ url('/') }}">Take me back</a>
        </div>
    </div>
@endsection
