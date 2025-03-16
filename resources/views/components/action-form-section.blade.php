@props(['submit'])

<div {{ $attributes->merge(['class' => 'md:grid md:grid-cols-3 md:gap-6']) }}>
    <x-section-title>
        <x-slot:title>
            {{ $title }}
        </x-slot:title>

        <x-slot:description>
            {{ $description }}
        </x-slot:description>
    </x-section-title>

    <div class="mt-5 md:mt-0 md:col-span-2">
        <form action="{{ $submit }}">
            @csrf

            <div class="shadow overflow-hidden sm:rounded-md">
                <div class="pt-4 pr-4 pb-4 pl-4 bg-primary sm:p-6">
                    <div class="grid grid-cols-6 gap-6">
                        {{ $form }}
                    </div>
                </div>

                @if (isset($actions))
                    <div class="flex items-center justify-end pl-4 pr-4 py-3 bg-tertiary text-right">
                        {{ $actions }}
                    </div>
                @endif
            </div>
        </form>
    </div>
</div>
