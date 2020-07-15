<!doctype html>
<html lang="{{ app()->getLocale() }}">
@include('website.layouts.components.header')

<body>
    @yield('content')

    @include('website.layouts.components.footer')
</body>
</html>
