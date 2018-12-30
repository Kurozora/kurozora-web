@extends('website.layouts.landing')

@section('content')
    <h1 id="kurozora-title">Redirecting you...</h1>

    <script>
        window.onload = function() {
            <!-- Deep link URL for existing users with app already installed on their device -->
            window.location = 'kurozora://anime/1';
        }
    </script>
@endsection