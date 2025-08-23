<x-app-layout>
    <x-slot:title>
        {{ __('Sign Up Child') }}
    </x-slot:title>

    <x-slot:header>
        <h2 class='text-2xl font-bold'>
            {{ __('Kurozora Account') }}
        </h2>
    </x-slot:header>

    <div class='flex flex-col justify-center w-screen h-full max-w-prose mx-auto pt-4 pb-6 pl-4 pr-4'>
        <div class='mb-5 text-center'>
            <h1 class='text-2xl font-bold'>{{ __('Invite Family') }}</h1>
            <p>{{ trans_choice('[6] You can add up to :x people to your family.|{1,5} You can add up to :x more people to your family.', auth()->user()->children_count, ['x' => number_to_words(6 - auth()->user()->children_count)]) }}</p>
        </div>

        <x-validation-errors class='mb-4' />

        <form method='POST' action="{{ route('sign-up.child') }}">
            @csrf
            <x-honey recaptcha='sign_up_child' />

            <section class='space-y-4'>
                <div>
                    <x-label for='username' value="{{ __('Username') }}" />
                    <x-input id='username' class='mt-1 block w-full' type='text' name='username'
                         :value="old('username')" placeholder="{{ __('Pick a cool one') }} 🙈" required autofocus
                         autocomplete='username' />
                </div>

                <div>
                    <x-label for='email' value="{{ __('Email') }}" />
                    <x-input id='email' class='mt-1 block w-full' type='email' name='email' :value="old('email')"
                         placeholder="{{ __('We all forget our passwords') }} 🙉" required />
                </div>

                <div>
                    <x-label for='password' value="{{ __('Password') }}" />
                    <x-input id='password' class='mt-1 block w-full' type='password' name='password'
                         placeholder="{{ __('Make it super secret') }} 🙊" required autocomplete='new-password'
                         passwordrules='minlength: 5; maxlength: 255; required: lower; required: upper; required: digit; required: special;' />
                </div>

                <div>
                    <x-label for='password_confirmation' value="{{ __('Confirm Password') }}" />
                    <x-input id='password_confirmation' class='mt-1 block w-full' type='password'
                         name='password_confirmation' placeholder="{{ __('But keep it memorable') }} 🐵" required
                         autocomplete='new-password'
                         passwordrules='minlength: 5; maxlength: 255; required: lower; required: upper; required: digit; required: special;' />
                </div>
            </section>

            <section class='flex flex-col items-center justify-end gap-4 mt-8 text-center'>
                <x-button>
                    {{ __('Join') }} 🤗
                </x-button>
            </section>
        </form>

        {{-- Services --}}
        <section class='flex flex-col items-center space-y-4 mt-16 text-center'>
            <x-picture class='max-w-sm'>
                <img src="{{ asset('images/static/promotional/kurozora_services.webp') }}"
                     alt="{{ __(':x services', ['x' => config('app.name')]) }}"
                     title="{{ __(':x services', ['x' => config('app.name')]) }}">
            </x-picture>

            <p class='text-sm'>{{ __('Your :x Account lets you access your library, favorites, reminders, reviews, and
                more on your devices, automatically.', ['x' => config('app.name')]) }}</p>
        </section>

        {{-- Legal --}}
        <section class='space-y-1 mt-16 text-center text-sm'>
            <p class='text-secondary'>{{ __('Your :x Account information is used to enable :x services when you sign in.
                :x services includes the library where you can keep track of the shows you are interested in.', ['x' =>
                config('app.name')]) }}</p>
            <x-link href="{{ route('legal.privacy-policy') }}" wire:navigate>{{ __('See how your data is managed…') }}
            </x-link>
        </section>
    </div>
</x-app-layout>
