<x-base-layout>
    <x-slot:title>
        {{ $title }}
    </x-slot>

    <x-slot:themeColor>
        {{ $themeColor ?? null }}
    </x-slot>

    <x-slot:lightThemeColor>
        {{ $lightThemeColor ?? null }}
    </x-slot>

    <x-slot:darkThemeColor>
        {{ $darkThemeColor ?? null }}
    </x-slot>

    <x-slot:meta>
        {{ $meta ?? '' }}
    </x-slot>

    <!-- Page Heading -->
    <header class="bg-gray-100 shadow">
        <div class="flex max-w-7xl mx-auto px-4 py-6 sm:px-6">
            {{ $header }}
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
</x-base-layout>
