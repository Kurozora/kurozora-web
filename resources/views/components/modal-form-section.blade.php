@props(['submit', 'id' => null, 'maxWidth' => null])

<x-modal :id="$id" :maxWidth="$maxWidth" {{ $attributes }}>
    <div class="px-6 py-4">
        <div class="text-lg">
            {{ $title }}
        </div>
    </div>

    <div class="mt-4 px-6 py-4">
        {{ $slot }}
    </div>
</x-modal>
