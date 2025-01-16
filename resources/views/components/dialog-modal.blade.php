@props(['id' => null, 'maxWidth' => null])

<x-modal :id="$id" :maxWidth="$maxWidth" {{ $attributes }}>
    <div class="px-6 pt-4 pb-4">
        <div class="text-lg">
            {{ $title }}
        </div>

        @if (isset($description))
            <p class="text-sm">
                {{ $description }}
            </p>
        @endif

        <x-hr class="mt-4 mb-4"/>

        <div class="mt-4">
            {{ $content }}
        </div>
    </div>

    <div class="px-6 pt-4 pb-4 bg-secondary text-right">
        {{ $footer }}
    </div>
</x-modal>
