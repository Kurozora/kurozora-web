<div class="md:grid md:grid-cols-3 md:gap-6" {{ $attributes }}>
    <x-section-title>
        <x-slot:title>
            {{ $title }}
        </x-slot:title>

        <x-slot:description>
            {{ $description }}
        </x-slot:description>
    </x-section-title>

    <div class="mt-5 md:mt-0 md:col-span-2">
        <div class="pl-4 pr-4 pt-4 pb-4 bg-primary shadow shadow-primary sm:rounded-lg sm:p-6">
            {{ $content }}
        </div>
    </div>
</div>
