<main>
    <x-slot:title>
        {{ __('Contact Kurozora') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Contact Kurozora support by email regarding the app, website, or other services. Kurozora support is here to help.') }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Contact') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Contact Kurozora support by email regarding the app, website, or other services. Kurozora support is here to help.') }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
    </x-slot:meta>

    <div class="max-w-7xl mx-auto pl-4 pr-4 py-6 sm:px-6">
        <x-action-form-section submit="mailto:kurozoraapp@gmail.com" method="GET" enctype="application/x-www-form-urlencoded">
            <x-slot:title>
                {{ __('Contact Us') }}
            </x-slot:title>

            <x-slot:description>
                {{ __('Send us an email with your question and we will help you. Clicking on send will open the mail app with the subject and message you provided in the form.') }}
            </x-slot:description>

            <x-slot:form>
                <div class="col-span-6 sm:col-span-4">
                    <x-label for="subject" value="{{ __('Subject') }}" />
                    <x-input id="subject" class="mt-1 block w-full" name="subject" type="text" autofocus />
                </div>

                <div class="col-span-6 sm:col-span-4">
                    <x-label for="body" value="{{ __('Message') }}" />
                    <x-textarea class="block w-full h-48 mt-1 resize-none" id="body" name="body"></x-textarea>
                </div>
            </x-slot:form>

            <x-slot:actions>
                <x-button type="submit">
                    {{ __('Send') }}
                </x-button>
            </x-slot:actions>
        </x-action-form-section>
    </div>
</main>
