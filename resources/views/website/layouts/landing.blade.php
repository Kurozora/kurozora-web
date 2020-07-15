<!doctype html>
<html lang="{{ app()->getLocale() }}">
@include('website.layouts.components.header')

<body>
    <div class="container mx-auto px-4">
        <div id="middle-div" class="relative hidden">
            @yield('content')
        </div>
    </div>

    @include('website.layouts.components.footer')

    <script>
        $('html').css('background-image', `url({{ asset('img/static/star_bg_lg.jpg') }})`);
        $(document).ready(function() {
            $('#middle-div').fadeIn(1000);
        });
    </script>
</body>
</html>
