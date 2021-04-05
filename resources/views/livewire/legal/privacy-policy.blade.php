<div class="container mx-auto p-6 pt-0 my-8 bg-white rounded">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Legal') }}
        </h2>
    </x-slot>

    <div class="text-center mt-16">
        <h1>Privacy Policy</h1>
    </div>
    {!! $privacyPolicyText !!}
    <div>
        <a href="{{ url('/') }}">Take me back</a>
    </div>
</div>
