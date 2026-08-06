@props(['id' => null, 'maxWidth' => null, 'stickyHeader' => false])

<x-modal :id="$id" :maxWidth="$maxWidth" {{ $attributes }}>
    <div>
        <div @class(['sticky top-0 bg-primary z-10' => $stickyHeader])>
            <div class="pt-4 pb-4 pl-4 pr-4">
                <p class="text-lg">
                    {{ $title }}
                </p>

                @if (isset($description))
                    <p class="text-sm">
                        {{ $description }}
                    </p>
                @endif
            </div>

            <x-hr />
        </div>

        <div>
            {{ $content }}
        </div>
    </div>

    <div class="px-6 pt-4 pb-4 bg-secondary text-right">
        {{ $footer }}
    </div>
</x-modal>
