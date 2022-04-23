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
        <div class="flex max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
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
