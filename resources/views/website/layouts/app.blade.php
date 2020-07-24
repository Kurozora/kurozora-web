<!doctype html>
<html lang="{{ app()->getLocale() }}">
@include('website.layouts.partials.header')

<body>
    @yield('content')

    @include('website.layouts.partials.footer')
</body>
</html>
