<div>
    <x-slot name="title">
        {{ __('Create Theme') }}
    </x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight self-center">
            {{ __('Create') }}
        </h2>
    </x-slot>

    <section class="container mx-auto px-4">
        <div class="text-center mt-6">
            <h1 class="text-2xl font-black">{{ __('Create your own theme') }}</h1>
            <p class="">{{ __('Take your time and make something great, we\'ll leave you to it.') }}</p>
        </div>

        <livewire:theme.theme-roller />
    </section>

    <section class="text-center mb-6">
        <h1 class="text-xl font-bold">{{ __('Finished designing your theme?') }}</h1>
        <p>{{ __ ('Use the button below to submit your theme to the Kurozora team for approval.') }}</p>

        <div class="mt-6">
            <x-button class="rounded-full font-bold">
                {{ __('Submit') }}
            </x-button>
        </div>
    </section>
</div>
