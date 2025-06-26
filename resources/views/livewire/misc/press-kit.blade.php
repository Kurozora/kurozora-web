<main>
    <x-slot:title>
        {{ __('Press-Kit') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Get Started! Download the full :x press-kit which includes logos and banners. Download Press-Kit.', ['x' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Press-Kit') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Get Started! Download the full :x press-kit which includes logos and banners. Download Press-Kit.', ['x' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('misc.press-kit') }}">
    </x-slot:meta>

    <div>
        {{-- Brand --}}
        <section class="relative pl-4 pr-4 pt-36 pb-10 overflow-hidden">
            <div
                class="absolute top-0 left-0 w-full h-full"
                style="background: url('{{ asset('images/static/backgrounds/background_blur.gif') }}') no-repeat center; background-size: cover; filter: blur(100px); transform: scale(3);"
            >
                <div class="w-full h-full bg-black/60"></div>
            </div>

            <div class="relative max-w-2xl mx-auto text-center drop-shadow">
                <div class="flex flex-col items-center">
                    <img class="mt-10 mb-4" width="82" height="82" src="{{ asset('images/static/icon/app_icon.gif') }}" alt="{{ config('app.name') }}">

                    <p class="text-xl font-semibold">{{ config('app.name') }}</p>

                    <p class="mt-2 text-4xl font-bold leading-tight tracking-tight">{{ __('Brand') }}</p>
                </div>

                <div class="mt-10">
                    <p class="text-lg font-light md:text-2xl">{{ __('Change is in the DNA of :x. This is reflected through the use of different color schemes in the logo artwork. That said, please keep it tasteful.', ['x' => config('app.name')]) }}</p>
                </div>
            </div>
        </section>

        {{-- Logo --}}
        <section class="pl-4 pr-4 pt-36 pb-10 bg-primary">
            <div class="flex flex-col items-center mx-auto max-w-7xl text-center">
                <p class="mt-2 text-4xl font-bold leading-tight tracking-tight">{{ __('Logo') }}</p>

                <div class="max-w-2xl mt-10">
                    <p class="text-lg font-light md:text-2xl">{{ __('Please do not distort the :x logo. You are free to edit, change, recolor, and reconfigure the :x logo as you see fit.', ['x' => config('app.name')]) }}</p>
                </div>
            </div>

            <div class="flex flex-wrap gap-4 m-auto mt-16 max-w-7xl">
                <div
                    class="relative flex items-center justify-center flex-grow w-64 md:w-80 bg-secondary pl-4 pr-4 pt-4 pb-4 border-2 rounded-3xl"
                    style="background: url('{{ asset('images/static/patterns/checkerboard_dark.svg') }}'); background-size: 24px;"
                >
                    <img class="mt-8 mb-8 pr-5 pl-5" src="https://raw.githubusercontent.com/Kurozora/kurozora-press-kit/master/Logos/4_Full_Logo_Lockup_Monochrome/full_logo_orange_RGB_monochrome.png" alt="{{ __(':x full logo orange RGB monochrome', ['x' => config('app.name')]) }}">

                    <div class="absolute right-0 bottom-0 pr-2 pb-2 pl-2">
                        <x-link href="https://github.com/Kurozora/kurozora-press-kit/raw/master/Logos/4_Full_Logo_Lockup_Monochrome/full_logo_orange_RGB_monochrome.svg" target="_blank" class="relative no-external-icon">.svg</x-link>
                        <x-link href="https://github.com/Kurozora/kurozora-press-kit/raw/master/Logos/4_Full_Logo_Lockup_Monochrome/full_logo_orange_RGB_monochrome.png" target="_blank" class="relative no-external-icon">.png</x-link>
                    </div>
                </div>

                <div
                    class="relative flex items-center justify-center flex-grow w-64 md:w-80 bg-secondary pl-4 pr-4 pt-4 pb-4 border-2 rounded-3xl"
                    style="background: url('{{ asset('images/static/patterns/checkerboard_light.svg') }}'); background-size: 24px;"
                >
                    <img class="mt-8 mb-8 pr-5 pl-5" src="https://raw.githubusercontent.com/Kurozora/kurozora-press-kit/master/Logos/4_Full_Logo_Lockup_Monochrome/full_logo_blueberry_RGB_monochrome.png" alt="{{ __(':x full logo blueberry RGB monochrome', ['x' => config('app.name')]) }}">

                    <div class="absolute right-0 bottom-0 pr-2 pb-2 pl-2">
                        <x-link href="https://github.com/Kurozora/kurozora-press-kit/raw/master/Logos/4_Full_Logo_Lockup_Monochrome/full_logo_blueberry_RGB_monochrome.svg" target="_blank" class="relative no-external-icon">.svg</x-link>
                        <x-link href="https://github.com/Kurozora/kurozora-press-kit/raw/master/Logos/4_Full_Logo_Lockup_Monochrome/full_logo_blueberry_RGB_monochrome.png" target="_blank" class="relative no-external-icon">.png</x-link>
                    </div>
                </div>

                <div
                    class="relative flex items-center justify-center flex-grow w-64 md:w-80 pl-4 pr-4 pt-4 pb-4 border-2 rounded-3xl"
                    style="background: url('{{ asset('images/static/patterns/checkerboard_dark.svg') }}'); background-size: 24px;"
                >
                    <img class="mt-8 mb-8 pr-5 pl-5" src="https://raw.githubusercontent.com/Kurozora/kurozora-press-kit/master/Logos/4_Full_Logo_Lockup_Monochrome/full_logo_white_RGB_monochrome.png" alt="{{ __(':x full logo white RGB monochrome', ['x' => config('app.name')]) }}">

                    <div class="absolute right-0 bottom-0 pr-2 pb-2 pl-2">
                        <x-link href="https://github.com/Kurozora/kurozora-press-kit/raw/master/Logos/4_Full_Logo_Lockup_Monochrome/full_logo_white_RGB_monochrome.svg" target="_blank" class="relative no-external-icon">.svg</x-link>
                        <x-link href="https://github.com/Kurozora/kurozora-press-kit/raw/master/Logos/4_Full_Logo_Lockup_Monochrome/full_logo_white_RGB_monochrome.png" target="_blank" class="relative no-external-icon">.png</x-link>
                    </div>
                </div>

                <div
                    class="relative flex items-center justify-center flex-grow w-64 md:w-80 bg-secondary pl-4 pr-4 pt-4 pb-4 border-2 rounded-3xl"
                    style="background: url('{{ asset('images/static/patterns/checkerboard_light.svg') }}'); background-size: 24px;"
                >
                    <img class="mt-8 mb-8 pr-5 pl-5" src="https://raw.githubusercontent.com/Kurozora/kurozora-press-kit/master/Logos/4_Full_Logo_Lockup_Monochrome/full_logo_black_RGB_monochrome.png" alt="{{ __(':x full logo black RGB monochrome', ['x' => config('app.name')]) }}">

                    <div class="absolute right-0 bottom-0 pr-2 pb-2 pl-2">
                        <x-link href="https://github.com/Kurozora/kurozora-press-kit/raw/master/Logos/4_Full_Logo_Lockup_Monochrome/full_logo_black_RGB_monochrome.svg" target="_blank" class="relative no-external-icon">.svg</x-link>
                        <x-link href="https://github.com/Kurozora/kurozora-press-kit/raw/master/Logos/4_Full_Logo_Lockup_Monochrome/full_logo_black_RGB_monochrome.png" target="_blank" class="relative no-external-icon">.png</x-link>
                    </div>
                </div>

                <div class="flex-grow w-64 md:w-80"></div>
                <div class="flex-grow w-64 md:w-80"></div>
            </div>
        </section>

        {{-- Spacing --}}
        <section class="pl-4 pr-4 pt-36 pb-10 bg-secondary">
            <div class="flex flex-col items-center mx-auto max-w-7xl text-center">
                <p class="mt-2 text-4xl font-bold leading-tight tracking-tight">{{ __('Spacing') }}</p>

                <div class="max-w-2xl mt-10">
                    <p class="text-lg font-light md:text-2xl">{{ __('The spacing around the :x logo is based on the letter "O". For a cleaner look, keep this rule of thumb in mind.', ['x' => config('app.name')]) }}</p>
                </div>
            </div>

            <div class="flex flex-wrap gap-4 m-auto mt-16 max-w-7xl">
                <div class="relative flex items-center justify-center flex-grow w-64 md:w-80 pl-4 pr-4 pt-4 pb-4">
                    <img src="{{ asset('images/static/spacing_guids.webp') }}" alt="{{ __(':x spacing guide', ['x' => config('app.name')]) }}">
                </div>
            </div>
        </section>

        {{-- Colors --}}
        <section class="pl-4 pr-4 pt-36 pb-10 bg-primary">
            <div class="flex flex-col items-center mx-auto max-w-7xl text-center">
                <p class="mt-2 text-4xl font-bold leading-tight tracking-tight">{{ __('Colors') }}</p>

                <div class="max-w-2xl mt-10">
                    <p class="text-lg font-light md:text-2xl">{{ __('The official :x colors are blueberry, orange and white. You are free to mix and combine, and use colors outside of the ones specified below.', ['x' => config('app.name')]) }}</p>
                </div>
            </div>

            <div class="flex flex-wrap gap-4 m-auto mt-16 max-w-7xl">
                <div class="flex flex-col justify-between flex-grow w-64 md:w-80 pl-4 pr-4 pt-4 pb-4 text-white border border-primary rounded-3xl shadow-lg" style="background-color: #353A50">
                    <p class="text-lg font-bold">{{ __('Blueberry') }}</p>
                    <div class="mt-12 font-semibold opacity-75">
                        <p>#353A50</p>
                        <p>RGB 53, 58, 80</p>
                        <p>CMYK 34, 28, 0, 69</p>
                        <p>HSL 229°, 20%, 26%</p>
                        <p>CIELab 24.83, 3.92, -14.05</p>
                    </div>
                </div>

                <div class="flex flex-col justify-between flex-grow w-64 md:w-80 pl-4 pr-4 pt-4 pb-4 bg-orange-500 text-white border border-primary rounded-3xl shadow-lg">
                    <p class="text-lg font-bold">{{ __('Orange') }}</p>
                    <div class="mt-12 font-semibold opacity-75">
                        <p>#FF9300</p>
                        <p>RGB 255, 147, 0</p>
                        <p>CMYK 0, 42, 100, 0</p>
                        <p>HSL 35°, 100%, 50%</p>
                        <p>CIELab 70.96, 33.26, 76.41</p>
                    </div>
                </div>

                <div class="flex flex-col justify-between flex-grow w-64 md:w-80 pl-4 pr-4 pt-4 pb-4 bg-yellow-500 text-white border border-primary rounded-3xl shadow-lg">
                    <p class="text-lg font-bold">{{ __('Lemon') }}</p>
                    <div class="mt-12 font-semibold opacity-75">
                        <p>#EAB308</p>
                        <p>RGB 234, 179, 8</p>
                        <p>CMYK 0, 24, 97, 8</p>
                        <p>HSL 45°, 93%, 47%</p>
                        <p>CIELab 75.92, 7.70, 77.63</p>
                    </div>
                </div>

                <div class="flex flex-col justify-between flex-grow w-64 md:w-80 pl-4 pr-4 pt-4 pb-4 bg-lime-500 text-white border border-primary rounded-3xl shadow-lg">
                    <p class="text-lg font-bold">{{ __('Lime') }}</p>
                    <div class="mt-12 font-semibold opacity-75">
                        <p>#84CC16</p>
                        <p>RGB 132, 204, 22</p>
                        <p>CMYK 35, 0, 89, 20</p>
                        <p>HSL 84°, 81%, 44%</p>
                        <p>CIELab 74.92, -46.78, 71.60</p>
                    </div>
                </div>

                <div class="flex flex-col justify-between flex-grow w-64 md:w-80 pl-4 pr-4 pt-4 pb-4 bg-sky-500 text-white border border-primary rounded-3xl shadow-lg">
                    <p class="text-lg font-bold">{{ __('Cerulean') }}</p>
                    <div class="mt-12 font-semibold opacity-75">
                        <p>#0EA5E9</p>
                        <p>RGB 14, 165, 233</p>
                        <p>CMYK 94, 29, 0, 9</p>
                        <p>HSL 199°, 89%, 48%</p>
                        <p>CIELab 64.07, -11.08, -43.87</p>
                    </div>
                </div>

                <div class="flex flex-col justify-between flex-grow w-64 md:w-80 pl-4 pr-4 pt-4 pb-4 bg-pink-500 text-white border border-primary rounded-3xl shadow-lg">
                    <p class="text-lg font-bold">{{ __('Raspberry') }}</p>
                    <div class="mt-12 font-semibold opacity-75">
                        <p>#EC4899</p>
                        <p>RGB 236, 72, 153</p>
                        <p>CMYK 0, 69, 35, 7</p>
                        <p>HSL 330°, 81%, 60%</p>
                        <p>CIELab 56.85, 68.82, -8.29</p>
                    </div>
                </div>

                <div class="flex flex-col justify-between flex-grow w-64 md:w-80 pl-4 pr-4 pt-4 pb-4 bg-red-500 text-white border border-primary rounded-3xl shadow-lg">
                    <p class="text-lg font-bold">{{ __('Strawberry') }}</p>
                    <div class="mt-12 font-semibold opacity-75">
                        <p>#EF4444</p>
                        <p>RGB 239, 68, 68</p>
                        <p>CMYK 0, 72, 72, 6</p>
                        <p>HSL 0°, 84%, 60%</p>
                        <p>CIELab 54.97, 64.70, 39.13</p>
                    </div>
                </div>

                <div class="flex flex-col justify-between flex-grow w-64 md:w-80 pl-4 pr-4 pt-4 pb-4 bg-white text-black border border-primary rounded-3xl shadow-lg">
                    <p class="text-lg font-bold">{{ __('White') }}</p>
                    <div class="mt-12 font-semibold opacity-75">
                        <p>#FFFFFF</p>
                        <p>RGB 255, 255, 255</p>
                        <p>CMYK 0, 0, 0, 0</p>
                        <p>HSL 0°, 0%, 100%</p>
                        <p>CIELab 100.00, 0.01, -0.01</p>
                    </div>
                </div>

                <div class="flex flex-col justify-between flex-grow w-64 md:w-80 pl-4 pr-4 pt-4 pb-4 bg-black text-white border border-primary rounded-3xl shadow-lg">
                    <p class="text-lg font-bold">{{ __('Black') }}</p>
                    <div class="mt-12 font-semibold opacity-75">
                        <p>#000000</p>
                        <p>RGB 0, 0, 0</p>
                        <p>CMYK 60, 60, 60, 100</p>
                        <p>HSL 0°, 0%, 0%</p>
                        <p>CIELab 0.00, 0.00, 0.00</p>
                    </div>
                </div>
                <div class="w-64 md:w-80 flex-grow"></div>
            </div>
        </section>

        {{-- Download Press-Kit --}}
        <section class="pl-4 pr-4 pt-36 pb-10 bg-secondary">
            <div class="flex flex-col items-center mx-auto max-w-7xl text-center">
                <img class="mt-10 mb-4" width="82" height="82" src="{{ asset('images/static/icon/app_icon.webp') }}" alt="{{ config('app.name') }}">

                <p class="mt-2 text-4xl font-bold leading-tight tracking-tight">{{ __('Get Started') }}</p>
            </div>

            <div class="max-w-7xl mx-auto mt-10 text-center">
                <p class="text-lg font-light md:text-2xl">{{ __('Download the full :x press-kit which includes logos and banners.', ['x' => config('app.name')]) }}</p>
            </div>

            <div class="flex flex-wrap gap-4 justify-center mt-12 pr-5 pl-5">
                <x-link-button class="text-lg" href="https://github.com/Kurozora/kurozora-press-kit/archive/refs/heads/master.zip" aria-label="Download the press-kit">{{ __('Download Press-Kit') }}</x-link-button>
            </div>
        </section>
    </div>
</main>
