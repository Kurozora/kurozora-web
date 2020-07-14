@extends('website.layouts.landing')

@section('content')
    <div class="container mx-auto">
        <div id="middle-div" class="relative h-screen hidden">
            <div class="absolute inset-0 flex flex-col items-center justify-center">
                <img src="{{ asset('img/static/logo_sm.png') }}" width="256" alt="Kurozora Logo"/>
                <br>
                <h1 class="text-white text-4xl font-bold">Kurozora</h1>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $('html').css('background-image', `url({{ asset('img/static/star_bg_lg.jpg') }})`);
        $(document).ready(function() {
            $('#middle-div').fadeIn(1000);
        });
    </script>
@endsection
