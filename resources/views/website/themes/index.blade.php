@extends('website.themes.template')

@section('content')
    <div class="section">
        <div class="container">
            {{-- Themes --}}
            <div class="columns is-multiline">
                @foreach($themes as $theme)
                    <div class="column is-one-quarter">
                        @component('website.themes.theme-thumbnail', ['theme' => $theme])
                        @endcomponent
                    </div>
                @endforeach
            </div>
            {{-- /Themes --}}

            {{-- Pagination --}}
            <nav>
                @if($themes->previousPageUrl())
                    <a href="{{ $themes->previousPageUrl() }}" class="button pagination-previous is-rounded">Previous page</a>
                @endif

                @if($themes->nextPageUrl())
                    <a href="{{ $themes->nextPageUrl() }}" class="button pagination-previous is-rounded is-pulled-right">Next page</a>
                @endif
            </nav>
            {{-- /Pagination --}}
        </div>
    </div>
@endsection