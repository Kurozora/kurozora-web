<main>
    <x-slot:title>
        {{ __('Contact :x', ['x' => config('app.name')]) }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Contact :x support by email regarding the app, website, or other services. :x support is here to help.', ['x' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Contact') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Contact :x support by email regarding the app, website, or other services. :x support is here to help.', ['x' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
    </x-slot:meta>

    <div class="pt-4 pb-6">
        {{-- Header --}}
        <section class="relative pl-4 pr-4 pt-36 pb-10 overflow-hidden xl:safe-area-inset">
            <div class="relative max-w-2xl mx-auto text-center">
                <div class="flex flex-col items-center">
                    <img class="mt-10 mb-4" width="82" height="82" src="{{ asset('images/static/icon/app_icon.webp') }}" alt="{{ config('app.name') }}">

                    <h1 class="text-4xl font-bold leading-tight tracking-tight">{{ __('Contact :x', ['x' => config('app.name')]) }}</h1>
                </div>

                <div class="mt-10">
                    <p class="text-lg font-light md:text-2xl">{{ __('Need help with :x? Whether you have a question, found an issue, or just want to share feedback, we’d love to hear from you!', ['x' => config('app.name')]) }}</p>
                </div>
            </div>
        </section>

        {{-- Suggestions --}}
        <section class="pt-36 pb-10 pl-4 pr-4 bg-secondary xl:safe-area-inset">
            <div class="max-w-7xl mx-auto pt-4 pb-6 pl-4 pr-4">
                <div class="flex flex-wrap justify-between gap-4">
                    <div class="flex flex-col w-full gap-2 md:w-3/4">
                        <h2 class="text-4xl font-semibold">{{ __('Quick Help') }}</h2>

                        <p class="text-lg font-light md:text-2xl">{{ __('Get the information you need without the wait. Browse our knowledge base for answers to common questions and important details about :x.', ['x' => config('app.name')]) }}</p>
                    </div>
                </div>

                <div class="flex flex-wrap justify-between gap-4 mt-10">
                    <a class="relative flex-grow w-64 bg-primary overflow-hidden rounded-lg md:w-80" href="{{ route('kb.iap') }}" wire:navigate>
                        <div class="flex flex-row items-center gap-2 pt-4 pb-4 pr-4 pl-4">
                            @svg('lock_fill', 'fill-current', ['width' => 24])

                            <p class="text-lg leading-tight">{{ __('In-App Purchases') }}</p>
                        </div>
                    </a>

                    <a class="relative flex-grow w-64 bg-primary overflow-hidden rounded-lg md:w-80" href="{{ route('kb.personalisation') }}" wire:navigate>
                        <div class="flex flex-row items-center gap-2 pt-4 pb-4 pr-4 pl-4">
                            @svg('person_fill', 'fill-current', ['width' => 24])

                            <p class="text-lg leading-tight">{{ __('About Personalisation') }}</p>
                        </div>
                    </a>

                    <a class="relative flex-grow w-64 bg-primary overflow-hidden rounded-lg md:w-80" href="{{ route('misc.press-kit') }}" wire:navigate>
                        <div class="flex flex-row items-center gap-2 pt-4 pb-4 pr-4 pl-4">
                            @svg('megaphone_fill', 'fill-current', ['width' => 24])

                            <p class="text-lg leading-tight">{{ __('Press-Kit') }}</p>
                        </div>
                    </a>

                    <a class="relative flex-grow w-64 bg-primary overflow-hidden rounded-lg md:w-80" href="{{ route('legal.privacy-policy') }}" wire:navigate>
                        <div class="flex flex-row items-center gap-2 pt-4 pb-4 pr-4 pl-4">
                            @svg('hand_raised_fill', 'fill-current', ['width' => 24])

                            <p class="text-lg leading-tight">{{ __('Privacy Policy') }}</p>
                        </div>
                    </a>

                    <a class="relative flex-grow w-64 bg-primary overflow-hidden rounded-lg md:w-80" href="{{ route('legal.terms-of-use') }}" wire:navigate>
                        <div class="flex flex-row items-center gap-2 pt-4 pb-4 pr-4 pl-4">
                            @svg('text_page_fill', 'fill-current', ['width' => 24])

                            <p class="text-lg leading-tight">{{ __('Terms of Use') }}</p>
                        </div>
                    </a>

                    <div class="relative flex-grow w-64 md:w-80"></div>
                    <div class="relative flex-grow w-64 md:w-80"></div>
                </div>
            </div>
        </section>

        {{-- E-mail --}}
        <section class="pt-36 pb-10 xl:safe-area-inset">
            <div class="max-w-7xl mx-auto pt-4 pb-6 pl-4 pr-4">
                <div class="flex flex-wrap justify-between gap-4">
                    <div class="flex flex-col w-full gap-2 md:w-3/4">
                        <h2 class="text-4xl font-semibold">{{ __('E-mail Us Directly') }}</h2>

                        <p class="text-lg font-light md:text-2xl">{{ __('Send us an email with your question and we will help you. Clicking on send will open the mail app with the subject and message you provided in the form.') }}</p>
                    </div>
                </div>

                <div class="mt-10">
                    <form action="mailto:kurozoraapp@gmail.com" method="GET" enctype="application/x-www-form-urlencoded">
                        <div class="bg-primary shadow shadow-primary overflow-hidden sm:rounded-md">
                            <div class="pt-4 pr-4 pb-4 pl-4 sm:p-6">
                                <div class="col-span-6 sm:col-span-4">
                                    <x-label for="subject" value="{{ __('Subject') }}" />
                                    <x-input id="subject" class="mt-1 block w-full" name="subject" type="text" autofocus />
                                </div>

                                <div class="mt-4 col-span-6 sm:col-span-4">
                                    <x-label for="body" value="{{ __('Message') }}" />
                                    <x-textarea class="block w-full h-48 mt-1 resize-none" id="body" name="body"></x-textarea>
                                </div>
                            </div>

                            <div class="flex items-center justify-end pl-4 pr-4 py-3 bg-secondary text-right">
                                <x-button type="submit">
                                    {{ __('Send') }}
                                </x-button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
</main>
