<main>
    <x-slot name="title">
        {{ __('Contact Kurozora') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Contact Kurozora support by email regarding the app, website, or other services. Kurozora support is here to help.') }}
    </x-slot>

    <x-slot name="meta">
        <meta property="og:title" content="{{ __('Contact') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Contact Kurozora support by email regarding the app, website, or other services. Kurozora support is here to help.') }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6">
        <x-action-form-section submit="mailto:kurozoraapp@gmail.com" method="GET" enctype="application/x-www-form-urlencoded">
            <x-slot name="title">
                {{ __('Contact Us') }}
            </x-slot>

            <x-slot name="description">
                {{ __('Send us an email with your question and we will help you. Clicking on send will open the mail app with the subject and message your provided in the form.') }}
            </x-slot>

            <x-slot name="form">
                <div class="col-span-6 sm:col-span-4">
                    <x-label for="subject" value="{{ __('Subject') }}" />
                    <x-input id="subject" name="subject" type="text" class="mt-1 block w-full" autofocus />
                </div>

                <div class="col-span-6 sm:col-span-4">
                    <x-label for="body" value="{{ __('Message') }}" />
                    <x-textarea class="block w-full h-48 mt-1 resize-none" id="body" name="body"></x-textarea>
                </div>
            </x-slot>

            <x-slot name="actions">
                <x-button type="submit">
                    {{ __('Send') }}
                </x-button>
            </x-slot>
        </x-action-form-section>
    </div>
</main>
