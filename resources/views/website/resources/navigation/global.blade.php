<nav id="globalnav" class="bg-grayBlue-500 {{ (Route::currentRouteName() == 'api.legal.privacy') ? '' : 'bg-opacity-25'}}">
    <div class="container gn-container">
        <global-navigation-content>
            <div class="gn-kurozora">
                <a class="gn-link gn-link-kurozora sm:hidden" href="{{ route('home') }}"></a>
            </div>

            <div class="text-sm gn-links">
                <a class="gn-link" href="{{ route('themes.index') }}">
                    Themes
                </a>
            </div>

            <div class="gn-actions">
                @if(Route::currentRouteName() == 'themes.index')
                    <a class="k-outline-button"
                       href="{{ (Route::currentRouteName() !== 'themes.create') ? route('themes.create') : '#' }}">
                        Create your own
                    </a>
                @endif
            </div>
        </global-navigation-content>
    </div>
</nav>
