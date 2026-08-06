<?php

namespace App\Http\Controllers\API\v1;

use App\Enums\WeeklyDigestSection;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetDigestSectionRequest;
use App\Http\Resources\WeeklyDigestSectionResource;
use App\Services\WeeklyDigestService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DigestController extends Controller
{
    /**
     * Returns the authenticated user's whole weekly digest.
     *
     * @param Request             $request
     * @param WeeklyDigestService $digestService
     *
     * @return JsonResponse
     */
    public function index(Request $request, WeeklyDigestService $digestService): JsonResponse
    {
        $digest = $digestService->buildAll($request->user(), $this->reference($request), true);

        return JSONResult::success([
            'data' => WeeklyDigestSectionResource::collection($this->sections($digest)),
        ]);
    }

    /**
     * Returns the section resources of a single named group of the weekly digest.
     *
     * @param GetDigestSectionRequest $request
     * @param WeeklyDigestService     $digestService
     * @param string                  $section
     *
     * @return JsonResponse
     */
    public function section(GetDigestSectionRequest $request, WeeklyDigestService $digestService, string $section): JsonResponse
    {
        $data = $digestService->buildSection($request->user(), $section, $this->reference($request), true);

        return JSONResult::success([
            'data' => WeeklyDigestSectionResource::collection($this->sections([$section => $data])),
        ]);
    }

    /**
     * Returns the optional reference date.
     *
     * @param Request $request
     *
     * @return Carbon|null
     */
    private function reference(Request $request): ?Carbon
    {
        return rescue(fn () => $request->filled('reference') ? Carbon::parse($request->input('reference')) : null, null, false);
    }

    /**
     * Flattens the built digest into an ordered list of non-empty section descriptors.
     *
     * @param array $digest
     *
     * @return array
     */
    private function sections(array $digest): array
    {
        $sections = [];
        $position = 0;
        $push = function (?array $section) use (&$sections, &$position) {
            if ($section !== null) {
                $section['position'] = $position++;
                $sections[] = $section;
            }
        };

        if (isset($digest[WeeklyDigestSection::Drops])) {
            $drops = $digest[WeeklyDigestSection::Drops];

            if (($drops['hero'] ?? null) !== null) {
                $push([
                    'kind' => 'hero',
                    'title' => null,
                    'subtitle' => $drops['heroCaption'],
                    'shows' => collect([$drops['hero']['model']]),
                ]);
            }

            $push($this->collectionSection('newEpisodes', __('New Episodes'), null, 'episodes', $drops['newEpisodes'] ?? collect()));
            $push($this->collectionSection('finales', __('Season Finales'), __('These wrap up this week.'), 'episodes', $drops['finales'] ?? collect()));
            $push($this->collectionSection('newReleases', __('New Releases'), null, 'games', $drops['newReleases'] ?? collect()));
        }

        if (isset($digest[WeeklyDigestSection::Recommendations])) {
            $recommendations = $digest[WeeklyDigestSection::Recommendations];
            $becauseYouWatched = $recommendations['becauseYouWatched'];

            if ($becauseYouWatched['anime'] !== null && $becauseYouWatched['relations']->isNotEmpty()) {
                $push([
                    'kind' => 'becauseYouWatched',
                    'title' => __('Because You Watched :title', ['title' => $becauseYouWatched['anime']->title]),
                    'subtitle' => null,
                    'shows' => $becauseYouWatched['relations']->map(fn ($relation) => $relation->related),
                ]);
            }

            if (($recommendations['dropIn'] ?? null) !== null) {
                $push([
                    'kind' => 'dropIn',
                    'title' => __('A Weekend Watch For You'),
                    'subtitle' => __('Highly rated, and not yet on your list.'),
                    'shows' => collect([$recommendations['dropIn']]),
                ]);
            }
        }

        if (isset($digest[WeeklyDigestSection::Rescue])) {
            $rescue = $digest[WeeklyDigestSection::Rescue];
            $push($this->collectionSection('rescueOnHold', __('Pick Up Where You Left Off'), __('On hold for a while. Ready to continue?'), 'shows', $rescue['onHold']));
            $push($this->collectionSection('rescuePlanning', __('Ready to Start?'), __('Sitting on your planning list for a while.'), 'shows', $rescue['planning']));
        }

        if (isset($digest[WeeklyDigestSection::UpNext])) {
            $upNext = $digest[WeeklyDigestSection::UpNext];
            $push($this->collectionSection('premiering', __('Premiering Soon'), __('On your list and about to start.'), 'shows', $upNext['premiering']));
            $push($this->collectionSection('releasing', __('Releasing Soon'), null, 'games', $upNext['releasing']));
        }

        if (isset($digest[WeeklyDigestSection::Trending])) {
            $push($this->collectionSection('trending', __('Trending This Week'), null, 'episodes', $digest[WeeklyDigestSection::Trending]['trending']));
        }

        if (isset($digest[WeeklyDigestSection::Birthdays])) {
            $push($this->collectionSection('birthdays', __('Birthdays This Week'), __('From the people making your favorite titles.'), 'people', $digest[WeeklyDigestSection::Birthdays]['birthdays']));
        }

        if (isset($digest[WeeklyDigestSection::Momentum])) {
            $momentum = $digest[WeeklyDigestSection::Momentum];

            if ($momentum['hasMomentum']) {
                $push([
                    'kind' => 'momentum',
                    'title' => null,
                    'subtitle' => null,
                    'momentum' => array_merge($momentum['momentum'], ['hasMomentum' => true]),
                ]);
            }
        }

        if (isset($digest[WeeklyDigestSection::Growth])) {
            $growth = $digest[WeeklyDigestSection::Growth];

            if ($growth['hasGrowth']) {
                $push([
                    'kind' => 'growth',
                    'title' => null,
                    'subtitle' => $growth['label'],
                ]);
            }
        }

        return $sections;
    }

    /**
     * Builds a section descriptor for a collection of identities.
     *
     * @param string      $kind
     * @param string      $title
     * @param string|null $subtitle
     * @param string      $relationship
     * @param Collection  $items
     *
     * @return array|null
     */
    private function collectionSection(string $kind, string $title, ?string $subtitle, string $relationship, Collection $items): ?array
    {
        if ($items->isEmpty()) {
            return null;
        }

        return [
            'kind' => $kind,
            'title' => $title,
            'subtitle' => $subtitle,
            $relationship => $items,
        ];
    }
}
