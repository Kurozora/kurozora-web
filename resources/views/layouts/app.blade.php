<x-base-layout>
    <x-slot:title>
        {{ $title }}
    </x-slot:title>

    <x-slot:themeColor>
        {{ $themeColor ?? null }}
    </x-slot:themeColor>

    <x-slot:lightThemeColor>
        {{ $lightThemeColor ?? null }}
    </x-slot:lightThemeColor>

    <x-slot:darkThemeColor>
        {{ $darkThemeColor ?? null }}
    </x-slot:darkThemeColor>

    <x-slot:meta>
        {{ $meta ?? '' }}
    </x-slot:meta>

    <!-- Page Heading -->
    <header class="bg-gray-100 shadow">
        <div class="flex max-w-7xl mx-auto pl-4 pr-4 py-6 sm:px-6">
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

    <x-slot:scripts>
        {{ $scripts ?? '' }}
    </x-slot:scripts>
</x-base-layout>
