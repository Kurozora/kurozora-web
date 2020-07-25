@extends('website.themes.template')

@section('content')
    <div class="container mx-auto px-4">
        {{-- Themes --}}
        <div class="flex flex-row flex-wrap">
            @foreach($themes as $theme)
                @component('website.themes.theme-thumbnail', ['theme' => $theme])
                @endcomponent
            @endforeach
        </div>
        {{-- /Themes --}}

        {{-- Pagination --}}
        {{ $themes->links() }}
        {{-- /Pagination --}}
    </div>
@endsection
