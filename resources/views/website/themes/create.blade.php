@extends('website.themes.template')

@section('content')
    <div class="section">
        <div class="container">
            <div class="has-text-centered">
                <h1 class="title white-text">Create your own theme</h1>
                <p class="subtitle white-text">Take your time and make something great, we'll leave you to it.</p>
            </div>

            <theme-roller submit_url="{{ route('themes.create') }}" />
        </div>
    </div>

    <div class="section has-background-light">
        <h1 class="title is-text has-text-centered">Finished designing your theme?</h1>
        <p class="subtitle has-text-centered">Use the button below to submit your theme to the Kurozora team for approval.</p>

        <div class="has-text-centered">
            <a class="button is-primary is-large" id="submit-theme">
                Submit
            </a>
        </div>
    </div>
@endsection