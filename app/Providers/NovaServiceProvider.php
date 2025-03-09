<?php

namespace App\Providers;

use App\Nova\Achievement;
use App\Nova\Activity;
use App\Nova\Anime;
use App\Nova\AnimeCast;
use App\Nova\AnimeTranslation;
use App\Nova\APIClientToken;
use App\Nova\AppTheme;
use App\Nova\CastRole;
use App\Nova\Character;
use App\Nova\CharacterTranslation;
use App\Nova\Comment;
use App\Nova\Country;
use App\Nova\Dashboards\Main;
use App\Nova\Dashboards\UserInsights;
use App\Nova\Episode;
use App\Nova\EpisodeTranslation;
use App\Nova\ExploreCategory;
use App\Nova\ExploreCategoryItem;
use App\Nova\Game;
use App\Nova\GameCast;
use App\Nova\GameTranslation;
use App\Nova\Genre;
use App\Nova\Language;
use App\Nova\Manga;
use App\Nova\MangaCast;
use App\Nova\MangaTranslation;
use App\Nova\Media;
use App\Nova\MediaGenre;
use App\Nova\MediaRating;
use App\Nova\MediaRelation;
use App\Nova\MediaSong;
use App\Nova\MediaStaff;
use App\Nova\MediaStat;
use App\Nova\MediaStudio;
use App\Nova\MediaTag;
use App\Nova\MediaTheme;
use App\Nova\MediaType;
use App\Nova\Notification;
use App\Nova\Permission;
use App\Nova\Person;
use App\Nova\PersonalAccessToken;
use App\Nova\Platform;
use App\Nova\Relation;
use App\Nova\Role;
use App\Nova\Season;
use App\Nova\SeasonTranslation;
use App\Nova\Session;
use App\Nova\SessionAttribute;
use App\Nova\Song;
use App\Nova\SongTranslation;
use App\Nova\Source;
use App\Nova\StaffRole;
use App\Nova\Status;
use App\Nova\Studio;
use App\Nova\Tag;
use App\Nova\Theme;
use App\Nova\TvRating;
use App\Nova\User;
use App\Nova\UserBlock;
use App\Nova\UserFavorite;
use App\Nova\UserLibrary;
use App\Nova\UserReminder;
use App\Nova\Video;
use App\Nova\View;
use App\Policies\PermissionPolicy;
use App\Policies\RolePolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Laravel\Nova\Actions\ActionEvent;
use Laravel\Nova\Menu\Menu;
use Laravel\Nova\Menu\MenuItem;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;
use Stepanenko3\LogsTool\LogsTool;
use Stepanenko3\NovaCommandRunner\CommandRunnerTool;
use Vyuldashev\NovaPermission\NovaPermissionTool;

if (class_exists('Laravel\Nova\NovaApplicationServiceProvider')) {
    class NovaServiceProvider extends NovaApplicationServiceProvider
    {
        /**
         * Bootstrap any application services.
         *
         * @return void
         */
        public function boot(): void
        {
            parent::boot();

            // Breadcrumbs
            Nova::withBreadcrumbs();

//            // Right-to-Left
//            Nova::enableRTL(function (Request $request) {
//                return $request->user()->wantsRTL();
//            });
            Nova::withoutNotificationCenter();
            // Set timezone to JST
            Nova::userTimezone(function (Request $request) {
                return 'Asia/Tokyo';
            });

            // Main Menu
            Nova::mainMenu(function (Request $request, Menu $menu) {
                return [
                    MenuSection::make(
                        Nova::__('Dashboards'),
                        collect($this->dashboards())
                            ->map(fn($dashboard) => $dashboard->menu($request))
                    )
                        ->collapsable()
                        ->icon('squares-2-x-2'),

                    MenuSection::make(
                        __('Explore'),
                        collect([
                            ExploreCategory::class,
                            ExploreCategoryItem::class,
                        ])->map(fn($resource) => MenuItem::resource($resource))
                    )
                        ->collapsable()
                        ->icon('magnifying-glass'),

                    MenuSection::make(
                        __('Anime'),
                        collect([
                            Anime::class,
                            AnimeTranslation::class,
                            AnimeCast::class,
                        ])->map(fn($resource) => MenuItem::resource($resource))
                    )
                        ->collapsable()
                        ->icon('tv'),

                    MenuSection::make(
                        __('Manga'),
                        collect([
                            Manga::class,
                            MangaTranslation::class,
                            MangaCast::class,
                        ])->map(fn($resource) => MenuItem::resource($resource))
                    )
                        ->collapsable()
                        ->icon('book-open'),

                    MenuSection::make(
                        __('Games'),
                        collect([
                            Game::class,
                            GameTranslation::class,
                            GameCast::class,
                        ])->map(fn($resource) => MenuItem::resource($resource))
                    )
                        ->collapsable()
                        ->icon('puzzle-piece'),

                    MenuSection::make(
                        __('Songs'),
                        collect([
                            Song::class,
                            SongTranslation::class,
                        ])->map(fn($resource) => MenuItem::resource($resource))
                    )
                        ->collapsable()
                        ->icon('musical-note'),

                    MenuSection::make(
                        __('Seasons & Episodes'),
                        collect([
                            Season::class,
                            SeasonTranslation::class,
                            Episode::class,
                            EpisodeTranslation::class,
                        ])->map(fn($resource) => MenuItem::resource($resource))
                    )
                        ->collapsable()
                        ->icon('film'),

                    MenuSection::make(
                        __('Characters & People'),
                        collect([
                            Character::class,
                            CharacterTranslation::class,
                            Person::class,
                            StaffRole::class,
                        ])->map(fn($resource) => MenuItem::resource($resource))
                    )
                        ->collapsable()
                        ->icon('face-smile'),

                    MenuSection::make(
                        __('Studios'),
                        collect([
                            Studio::class,
                            StaffRole::class,
                        ])->map(fn($resource) => MenuItem::resource($resource))
                    )
                        ->collapsable()
                        ->icon('building-office-2'),

                    MenuSection::make(
                        __('Platforms'),
                        collect([
                            Platform::class,
                        ])->map(fn($resource) => MenuItem::resource($resource))
                    )
                        ->collapsable()
                        ->icon('device-phone-mobile'),

                    MenuSection::make(
                        __('Media'),
                        collect([
                            Media::class,
                            MediaGenre::class,
                            MediaRating::class,
                            MediaRelation::class,
                            MediaSong::class,
                            MediaStaff::class,
                            MediaStat::class,
                            MediaStudio::class,
                            MediaTag::class,
                            MediaTheme::class,
                            MediaType::class,
                            Video::class,
                        ])->map(fn($resource) => MenuItem::resource($resource))
                    )
                        ->collapsable()
                        ->icon('squares-2x2'),

                    MenuSection::make(
                        __('Attributes'),
                        collect([
                            CastRole::class,
                            Genre::class,
                            Relation::class,
                            Source::class,
                            Status::class,
                            Tag::class,
                            Theme::class,
                            TvRating::class,
                        ])->map(fn($resource) => MenuItem::resource($resource))
                    )
                        ->collapsable()
                        ->icon('squares-plus'),

                    MenuSection::make(
                        __('Localization'),
                        collect([
                            Country::class,
                            Language::class,
                        ])->map(fn($resource) => MenuItem::resource($resource))
                    )
                        ->collapsable()
                        ->icon('language'),

                    MenuSection::make(
                        __('Cosmetics'),
                        collect([
                            Achievement::class,
                            AppTheme::class,
                        ])->map(fn($resource) => MenuItem::resource($resource))
                    )
                        ->collapsable()
                        ->icon('sparkles'),

                    MenuSection::make(
                        __('User Activity'),
                        collect([
                            Activity::class,
                            View::class,
                            Comment::class,
                        ])->map(fn($resource) => MenuItem::resource($resource))
                    )
                        ->collapsable()
                        ->icon('chat-bubble-left-right'),

                    MenuSection::make(
                        __('Users & Permissions'),
                        collect([
                            User::class,
                            APIClientToken::class,
                            UserBlock::class,
                            UserFavorite::class,
                            UserLibrary::class,
                            UserReminder::class,
                            Notification::class,
                            Permission::class,
                            Role::class,
                        ])->map(fn($resource) => MenuItem::resource($resource))
                    )
                        ->collapsable()
                        ->icon('user'),

                    MenuSection::make(
                        __('Sessions'),
                        collect([
                            PersonalAccessToken::class,
                            Session::class,
                            SessionAttribute::class,
                        ])->map(fn($resource) => MenuItem::resource($resource))
                    )
                        ->collapsable()
                        ->icon('arrow-left-end-on-rectangle'),

                    collect($this->tools())
                        ->map(fn($tool) => $tool->menu($request)),
                ];
            });

            // User Menu
            Nova::userMenu(function (Request $request, Menu $menu) {
                $menu->append([
                    MenuItem::link('Profile', '/resources/' . User::uriKey() . '/' . $request->user()->getKey())
                ]);

                $menu->append([
                    MenuItem::make(__('Settings'))
                        ->path(route('settings'))
                        ->external(),
                ]);

                return $menu;
            });
        }

        /**
         * Register the Nova gate.
         *
         * This gate determines who can access Nova in non-local environments.
         *
         * @return void
         */
        protected function gate(): void
        {
            Gate::define('viewNova', function (\App\Models\User $user) {
                if (!$user->hasRole(['superAdmin', 'admin', 'mod', 'editor'])) {
                    abort(redirect('https://discord.com/channels/449250093623934977/1021013118408724480/1021150221054521344'));
                }
                return true;
            });
        }

        /**
         * Register the Nova routes.
         *
         * @return void
         */
        protected function routes(): void
        {
            Nova::routes()
                ->register();

            // Override routes with our own, since Nova 5 doesn't support
            // the 'nova.routes' config anymore.
            Nova::routes()->loginPath = config('nova.routes.login');
            Nova::routes()->logoutPath = config('nova.routes.logout');
            Nova::routes()->forgotPasswordPath = config('nova.routes.forgot_password');
            Nova::routes()->resetPasswordPath = config('nova.routes.reset_password');
        }

        /**
         * Configure the Nova authorization services.
         *
         * @return void
         */
        protected function authorization(): void
        {
            $this->gate();

            Nova::auth(function ($request) {
                return Gate::check('viewNova', [$request->user()]);
            });
        }

        /**
         * Get the dashboards that should be listed in the Nova sidebar.
         *
         * @return array
         */
        protected function dashboards(): array
        {
            return [
                Main::make(),
                UserInsights::make()
                    ->showRefreshButton()
                    ->canSee(function ($request) {
                        return $request->user()->hasRole('superAdmin');
                    }),
            ];
        }

        /**
         * Get the tools that should be listed in the Nova sidebar.
         *
         * @return array
         */
        public function tools(): array
        {
            return [
                (new CommandRunnerTool)
                    ->canSee(function ($request) {
                        return $request->user()?->hasRole('superAdmin') ?? false;
                    }),
                (new NovaPermissionTool)
                    ->rolePolicy(RolePolicy::class)
                    ->permissionPolicy(PermissionPolicy::class)
                    ->roleResource(Role::class)
                    ->permissionResource(Permission::class),
                (new LogsTool)
                    ->canSee(function ($request) {
                        return $request->user()?->hasRole('superAdmin') ?? false;
                    })
                    ->canDownload(function ($request) {
                        return $request->user()?->hasRole('superAdmin') ?? false;
                    })
                    ->canDelete(function ($request) {
                        return $request->user()?->hasRole('superAdmin') ?? false;
                    }),
            ];
        }

        /**
         * Register any application services.
         *
         * @return void
         */
        public function register(): void
        {
            parent::register();

            // Disable action events
            ActionEvent::saving(function ($actionEvent) {
                return false;
            });
        }
    }
}
