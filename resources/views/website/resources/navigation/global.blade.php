<nav id="globalnav" class="bg-grayBlue-500 bg-opacity-25">
    <div class="container flex items-center justify-between flex-wrap mx-auto px-4 py-2 h-full">
        <div class="flex items-center flex-shrink-0 text-white mr-6">
            <span class="font-semibold text-xl tracking-tight">Kurozora</span>
        </div>

        <div class="block lg:hidden">
            <button class="flex items-center px-3 py-2 border rounded text-orange-500 border-orange-500 hover:text-orange-400 hover:border-orange-400">
                <svg class="fill-current h-3 w-3" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <title>Menu</title>
                    <path d="M0 3h20v2H0V3zm0 6h20v2H0V9zm0 6h20v2H0v-2z"/>
                </svg>
            </button>
        </div>

        <div class="w-full block flex-grow lg:flex lg:items-center lg:w-auto">
            <div class="text-sm lg:flex-grow">
                <a class="gn-link" href="{{ route('home') }}">
                    Home
                </a>

                <a class="gn-link" href="{{ route('themes.index') }}">
                    Themes
                </a>
            </div>

            <div>
                <a class="k-outline-button {{ (Route::currentRouteName() == 'themes.index') ? '' : 'hidden' }}"
                   href="{{ (Route::currentRouteName() !== 'themes.create') ? route('themes.create') : '#' }}">
                    Create your own
                </a>
            </div>
        </div>
    </div>
</nav>
