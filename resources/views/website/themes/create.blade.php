@extends('website.themes.template')

@section('content')
    <section class="container mx-auto px-4">
        <div class="text-center">
            <h1 class="text-white">Create your own theme</h1>
            <p class="text-white">Take your time and make something great, we'll leave you to it.</p>
        </div>

        <theme-roller submit_url="{{ route('themes.create') }}"></theme-roller>
    </section>

    <section class="bg-white text-center">
        <h1>Finished designing your theme?</h1>
        <p>Use the button below to submit your theme to the Kurozora team for approval.</p>

        <div class="pb-6">
            <a class="k-button" id="submit-theme">
                Submit
            </a>
        </div>
    </section>
@endsection
