<x-base-layout>
    <x-slot:title>
        {{ $title }}
    </x-slot:title>

    <x-slot:meta>
        {{ $meta ?? '' }}
    </x-slot:meta>

    <x-slot:styles>
        {{ $styles ?? '' }}
    </x-slot:styles>

    <!-- Page Heading -->
    <header class="bg-secondary shadow">
        <div class="xl:safe-area-inset">
            <div class="flex pt-4 pb-6 pl-4 pr-4">
                {{ $header }}
            </div>
        </div>
    </header>

    <!-- Page Content -->
    <main class="mx-auto opacity-0">
        {{ $slot }}
    </main>

    <script>
        (function() {
            let mainElement = document.querySelector("main");
            mainElement.style.transition = "opacity 1s";
            mainElement.style.opacity = "1";
        })();
    </script>

    <x-slot:scripts>
        {{ $scripts ?? '' }}
    </x-slot:scripts>
</x-base-layout>
