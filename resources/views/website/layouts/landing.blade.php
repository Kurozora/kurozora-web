<!doctype html>
<html lang="{{ app()->getLocale() }}">
@include('website.layouts.partials.header')

<body>
    <div id="app">
        @include('website.resources.navigation.global')

        <main id="main" class="container mx-auto px-4 hidden">
            @yield('content')
        </main>

        @include('website.layouts.partials.footer')
    </div>

    <script>
        $('html').css('background-image', `url({{ asset('img/static/star_bg_lg.jpg') }})`);
        $(document).ready(function() {
            $('#main').fadeIn(1000);
        });
    </script>
</body>
</html>
