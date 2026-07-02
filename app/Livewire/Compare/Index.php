<?php

namespace App\Livewire\Compare;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\File;
use Livewire\Component;

class Index extends Component
{
    /**
     * The raw comparison data.
     *
     * @return array
     * @throws FileNotFoundException
     */
    public function getComparisonProperty(): array
    {
        return File::json(resource_path('docs/features.json'));
    }

    /**
     * The compared services, Kurozora first.
     *
     * @return array
     */
    public function getServicesProperty(): array
    {
        return collect($this->comparison['services'])
            ->map(fn (array $service) => [
                'id' => $service['id'],
                'name' => $service['name'],
                'note' => isset($service['note']) ? __($service['note']) : null,
            ])
            ->all();
    }

    /**
     * The feature rows ready for display, cells aligned to the services order.
     *
     * @return array
     */
    public function getFeatureRowsProperty(): array
    {
        $services = $this->comparison['services'];

        return collect($this->comparison['features'])
            ->map(function (array $feature) use ($services) {
                $cells = [];
                $notes = [];

                foreach ($services as $service) {
                    $support = $feature['support'][$service['id']] ?? ['value' => 'unknown'];
                    $display = $this->displayFor($support['value']);

                    if (is_string($display) && isset($support['since'])) {
                        $display .= ' (' . $support['since'] . ')';
                    }

                    $cells[] = ['display' => $display];

                    if (isset($support['note'])) {
                        $notes[] = [
                            'service' => $service['name'],
                            'note' => __($support['note']),
                        ];
                    }
                }

                return [
                    'label' => __($feature['label']),
                    'description' => isset($feature['description']) ? __($feature['description']) : null,
                    'cells' => $cells,
                    'notes' => $notes,
                ];
            })
            ->all();
    }

    /**
     * The GitHub URL of the comparison data file.
     *
     * @return string
     */
    public function getDataFileUrlProperty(): string
    {
        return config('social.github.url') . '/kurozora-web/blob/master/resources/docs/features.json';
    }

    /**
     * The GitHub URL for reporting a problem with the comparison data.
     *
     * @return string
     */
    public function getReportProblemUrlProperty(): string
    {
        return config('social.github.url') . '/kurozora-web/issues/new?' . http_build_query([
            'title' => 'Comparison data: <summarize the problem>',
            'body' => '**Page:** ' . route('compare.index') . "\n**Data file:** `resources/docs/features.json`\n\n**What is wrong?**\n\n",
        ]);
    }

    /**
     * The per-service comparison write-ups ready for display.
     *
     * @return array
     */
    public function getServiceComparisonsProperty(): array
    {
        return [
            [
                'title' => __(':x vs. AniDB', ['x' => config('app.name')]),
                'paragraph' => __('AniDB tracks anime at a depth nobody else attempts, down to the individual file, and it’s completely free of ads. It’s built for the archival crowd: no official apps and no push notifications, on purpose. Kurozora aims at everyday watching instead, a push the moment something airs, calendar integration, and a yearly Re:CAP of it all.'),
            ],
            [
                'title' => __(':x vs. MyAnimeList', ['x' => config('app.name')]),
                'paragraph' => __('MyAnimeList is where most people start, and its community is by far the biggest. The site itself is showing its age though: the iPhone app hasn’t seen an update since early 2024, there are no push notifications, and removing ads costs $2.99 a month. Kurozora sends a free push the moment an episode airs, catalogs theme songs with synced lyrics, recaps your year, and imports your MyAnimeList library in one step.'),
            ],
            [
                'title' => __(':x vs. AniList', ['x' => config('app.name')]),
                'paragraph' => __('AniList has a clean site and flexible lists, but there’s no official app, and airing notifications only show up in the on-site bell. Kurozora ships its own apps for iPhone, iPad, and Mac, notifies your device when episodes air, and covers theme songs with synced lyrics, which AniList doesn’t index at all.'),
            ],
            [
                'title' => __(':x vs. Anime-Planet', ['x' => config('app.name')]),
                'paragraph' => __('Anime-Planet has been around since 2001 and its hand-made recommendations are still the best reason to visit. It has no apps and no notifications, and going ad-free costs $5 a month on Patreon. Kurozora is ad-free for everyone, tells you the moment an episode airs, and lives on your phone, tablet, and Mac.'),
            ],
            [
                'title' => __(':x vs. Kitsu', ['x' => config('app.name')]),
                'paragraph' => __('Kitsu still runs, but its mobile apps were pulled from the stores and the website hasn’t seen real development since early 2025. If you’re on Kitsu, your library moves over to Kurozora in a single import, so nothing is lost.'),
            ],
            [
                'title' => __(':x vs. Notify.moe', ['x' => config('app.name')]),
                'paragraph' => __('Notify.moe is a lovely, minimal tracker, but its developer moved on in 2022 and the project has been archived since. Kurozora is actively developed and isn’t going anywhere.'),
            ],
            [
                'title' => __(':x vs. LiveChart', ['x' => config('app.name')]),
                'paragraph' => __('LiveChart is the best seasonal chart on the web and its episode notifications are free. Tracking on it stays light though, with no character pages and no manga, and the app is Android-only, with ads you can’t pay to remove. Kurozora pairs the same airing alerts with per-episode progress, characters and voice actors, reviews, and a yearly Re:CAP.'),
            ],
            [
                'title' => __(':x vs. Simkl', ['x' => config('app.name')]),
                'paragraph' => __('Simkl combines anime with TV and movies and imports from more services than anyone else. But much of the everyday experience sits behind PRO or VIP: rewatch tracking, custom lists, recommendations, calendar feeds, the Year in Review, even choosing romaji titles. It doesn’t track manga or games, and its iOS app was last updated in 2022. On Kurozora, rewatch tracking and title languages are free. So is the fan layer Simkl skips: characters, voice actors, theme songs with synced lyrics, and parental guides.'),
            ],
            [
                'title' => __(':x vs. Trakt', ['x' => config('app.name')]),
                'paragraph' => __('Trakt is a TV-and-movies tracker first, with a very active app. Anime is served through TMDB data, which is where the season-numbering headaches come from, and the calendar plus Year in Review live behind the $60-a-year VIP tier. Kurozora is anime-first, so numbering just works, and your yearly Re:CAP is free.'),
            ],
            [
                'title' => __(':x vs. TheTVDB, IMDb & TMDB', ['x' => config('app.name')]),
                'paragraph' => __('These three are metadata databases more than trackers. TheTVDB offers favorites, IMDb has check-ins and episode ratings but no watch progress, and TMDB stops at watchlists and ratings. They’re great at what they do, but a watch library is not what they do.'),
            ],
        ];
    }

    /**
     * The frequently asked questions ready for display.
     *
     * @return array
     */
    public function getFrequentlyAskedQuestionsProperty(): array
    {
        return [
            [
                'question' => __('Which anime tracker is best?'),
                'answer' => __('It depends on what you care about. MyAnimeList has the biggest community, AniDB has the deepest data, and Anime-Planet has the best hand-made recommendations. Kurozora is built to do the whole job in one place, from tracking and notifications to characters, music, achievements, and parental guides, with no ads on the web or in the apps.'),
            ],
            [
                'question' => __('Which anime trackers have no ads?'),
                'answer' => __('Kurozora, AniDB, Notify.moe, and TMDB show no ads at all. MyAnimeList, AniList, Anime-Planet, Simkl, Trakt, and TheTVDB charge to remove theirs, and LiveChart has ads you can’t remove at any price.'),
            ],
            [
                'question' => __('What is the best MyAnimeList alternative?'),
                'answer' => __('AniList, if you mostly want a nicer website. Kurozora, if you want native apps, free episode notifications, synced theme-song lyrics, and a library that also covers manga and games. Your MyAnimeList export drops straight in.'),
            ],
        ];
    }

    /**
     * The display value of a support entry.
     *
     * @param bool|string $supportValue
     *
     * @return bool|string
     */
    protected function displayFor(bool|string $supportValue): bool|string
    {
        return match ($supportValue) {
            true => true,
            false => false,
            'partial' => __('Partial'),
            'free' => __('Free'),
            'paid' => __('Paid'),
            'plus' => __('Kurozora+'),
            'site-only' => __('Site only'),
            'browser' => __('Browser'),
            'unmaintained' => __('Unmaintained'),
            'discontinued' => __('Discontinued'),
            default => '—',
        };
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.compare.index');
    }
}
