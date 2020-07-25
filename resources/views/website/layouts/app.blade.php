<!doctype html>
<html lang="{{ app()->getLocale() }}">
@include('website.layouts.partials.header')

<body>
    @include('website.resources.navigation.global')

    <main id="app">
        @yield('content')
    </main>

    @include('website.layouts.partials.footer')
</body>
</html>
