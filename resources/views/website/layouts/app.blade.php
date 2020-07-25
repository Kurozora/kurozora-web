<!doctype html>
<html lang="{{ app()->getLocale() }}">
@include('website.layouts.partials.header')

<body>
    <div id="app">
        @component('website.resources.navigation.global')
        @endcomponent

        <main id="main">
            @yield('content')
        </main>

        @include('website.layouts.partials.footer')
    </div>
</body>
</html>
