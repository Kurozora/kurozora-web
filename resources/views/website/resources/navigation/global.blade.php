<nav id="globalnav" class="bg-grayBlue-500 bg-opacity-25">
    <div class="container flex items-center justify-between flex-wrap mx-auto px-4 py-2 h-full">
        <div class="flex items-center flex-shrink-0 text-white mr-6">
            <span class="font-semibold text-xl tracking-tight">Kurozora</span>
        </div>

        <global-navigation-content>
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
        </global-navigation-content>
    </div>
</nav>
