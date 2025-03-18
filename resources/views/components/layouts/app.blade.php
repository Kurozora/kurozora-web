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

    <x-slot:styles>
        {{ $styles ?? '' }}
    </x-slot:styles>

    <!-- Page Heading -->
    <header class="bg-secondary shadow">
        <div class="flex pl-4 pr-4 py-6">
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
