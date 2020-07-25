<!doctype html>
<html lang="{{ app()->getLocale() }}">
@include('website.layouts.partials.header')

<body>
    @include('website.resources.navigation.global')

    <main id="app" class="container mx-auto px-4 hidden">
        @yield('content')
    </main>

    @include('website.layouts.partials.footer')

    <script>
        $('html').css('background-image', `url({{ asset('img/static/star_bg_lg.jpg') }})`);
        $(document).ready(function() {
            $('#app').fadeIn(1000);
        });
    </script>
</body>
</html>
