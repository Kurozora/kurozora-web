@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => config('app.url')])
            <img height="50px" width="50px" src="{{ asset('images/static/icon/logo.png') }}" alt="{{ config('app.name') }}">
        @endcomponent
    @endslot

    {{-- Body --}}
    {{ $slot }}

    {{-- Subcopy --}}
    @isset($subcopy)
        @slot('subcopy')
            @component('mail::subcopy')
                {{ $subcopy }}
            @endcomponent
        @endslot
    @endisset

    {{-- Footer --}}
    @slot('footer')
        @component('mail::footer')
            Copyright © {{ date('Y') }} {{ config('app.name') }} B.V. @lang('All rights reserved.')
        @endcomponent
    @endslot
@endcomponent
