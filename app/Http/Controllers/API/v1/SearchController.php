<?php

namespace App\Http\Controllers\API\v1;

use App\Enums\SearchScope;
use App\Enums\SearchType;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Http\Resources\AnimeResourceIdentity;
use App\Http\Resources\CharacterResourceIdentity;
use App\Http\Resources\EpisodeResourceIdentity;
use App\Http\Resources\GameResourceIdentity;
use App\Http\Resources\LiteratureResourceIdentity;
use App\Http\Resources\PersonResourceIdentity;
use App\Http\Resources\SongResourceIdentity;
use App\Http\Resources\StudioResourceIdentity;
use App\Http\Resources\UserResourceIdentity;
use App\Models\Anime;
use App\Models\Character;
use App\Models\Episode;
use App\Models\Game;
use App\Models\Manga;
use App\Models\Person;
use App\Models\Song;
use App\Models\Studio;
use App\Models\User;
use App\Models\UserLibrary;
use App\Scopes\IgnoreListScope;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator as ConcreteLengthAwarePaginator;
use Laravel\Scout\Builder;
use Uri;

class SearchController extends Controller
{
    /**
     * The weekday filters read in the requester's timezone.
     */
    private const array SCHEDULE_DAY_FIELDS = ['air_day', 'publication_day'];

    /**
     * The part of day filters and the attribute each one reads.
     */
    private const array DAY_PART_FIELDS = [
        'air_time_part' => 'air_time_minutes',
        'publication_time_part' => 'publication_time_minutes',
    ];

    /**
     * The start and end hour of every part of the day.
     */
    private const array DAY_PARTS = [
        0 => [23, 6],
        1 => [6, 12],
        2 => [12, 18],
        3 => [18, 23],
    ];

    /**
     * Retrieves search results of the given type.
     *
     * @param SearchRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function index(SearchRequest $request): JsonResponse
    {
        $data = $request->validated();
        $scope = $data['scope'];
        $types = $data['types'];

        if ($scope == SearchScope::Library && !auth()->check()) {
            throw new AuthenticationException('The request wasn’t accepted due to an issue with the credentials.');
        }

        $response = [];
        foreach ($types as $type) {
            switch ($type) {
                case SearchType::Characters:
                    $resource = Character::search($data['query'] ?? '');
                    $this->filter(Character::class, $request, $resource);
                    $resource = $resource->paginate($data['limit'] ?? 20)
                        ->appends($data);

                    $nextPageURL = $this->nextPageUrlFor($request, $resource, $type);

                    $response[$type] = [
                        'data' => CharacterResourceIdentity::collection($resource),
                        'next' => empty($nextPageURL) ? null : $nextPageURL
                    ];
                    break;
                case SearchType::Episodes:
                    $resource = Episode::search($data['query'] ?? '');
                    $this->filter(Episode::class, $request, $resource);
                    $resource = $resource->paginate($data['limit'] ?? 20)
                        ->appends($data);

                    $nextPageURL = $this->nextPageUrlFor($request, $resource, $type);

                    $response[$type] = [
                        'data' => EpisodeResourceIdentity::collection($resource),
                        'next' => empty($nextPageURL) ? null : $nextPageURL
                    ];
                    break;
                case SearchType::Games:
                    if ($scope == SearchScope::Library) {
                        $resource = $this->libraryScopedSearch(Game::class, $data['query'] ?? '', $request, $data['limit'] ?? 5);
                    } else {
                        $resource = Game::search($data['query'] ?? '');
                        $this->filter(Game::class, $request, $resource);
                        $resource = $resource->paginate($data['limit'] ?? 5)
                            ->appends($data);
                    }

                    $nextPageURL = $this->nextPageUrlFor($request, $resource, $type);

                    $response[$type] = [
                        'data' => GameResourceIdentity::collection($resource),
                        'next' => empty($nextPageURL) ? null : $nextPageURL
                    ];
                    break;
                case SearchType::Literatures:
                    if ($scope == SearchScope::Library) {
                        $resource = $this->libraryScopedSearch(Manga::class, $data['query'] ?? '', $request, $data['limit'] ?? 5);
                    } else {
                        $resource = Manga::search($data['query'] ?? '');
                        $this->filter(Manga::class, $request, $resource);
                        $resource = $resource->paginate($data['limit'] ?? 5)
                            ->appends($data);
                    }

                    $nextPageURL = $this->nextPageUrlFor($request, $resource, $type);

                    $response[$type] = [
                        'data' => LiteratureResourceIdentity::collection($resource),
                        'next' => empty($nextPageURL) ? null : $nextPageURL
                    ];
                    break;
                case SearchType::People:
                    $resource = Person::search($data['query'] ?? '');
                    $this->filter(Person::class, $request, $resource);
                    $resource = $resource->paginate($data['limit'] ?? 5)
                        ->appends($data);

                    $nextPageURL = $this->nextPageUrlFor($request, $resource, $type);

                    $response[$type] = [
                        'data' => PersonResourceIdentity::collection($resource),
                        'next' => empty($nextPageURL) ? null : $nextPageURL
                    ];
                    break;
                case SearchType::Shows:
                    if ($scope == SearchScope::Library) {
                        $resource = $this->libraryScopedSearch(Anime::class, $data['query'] ?? '', $request, $data['limit'] ?? 5);
                    } else {
                        $resource = Anime::search($data['query'] ?? '');
                        $this->filter(Anime::class, $request, $resource);
                        $resource = $resource->simplePaginate($data['limit'] ?? 5)
                            ->appends($data);
                    }

                    $nextPageURL = $this->nextPageUrlFor($request, $resource, $type);

                    $response[$type] = [
                        'data' => AnimeResourceIdentity::collection($resource),
                        'next' => empty($nextPageURL) ? null : $nextPageURL
                    ];
                    break;
                case SearchType::Songs:
                    $resource = Song::search($data['query'] ?? '');
                    $this->filter(Song::class, $request, $resource);
                    $resource = $resource->paginate($data['limit'] ?? 5)
                        ->appends($data);

                    $nextPageURL = $this->nextPageUrlFor($request, $resource, $type);

                    $response[$type] = [
                        'data' => SongResourceIdentity::collection($resource),
                        'next' => empty($nextPageURL) ? null : $nextPageURL
                    ];
                    break;
                case SearchType::Studios:
                    $resource = Studio::search($data['query'] ?? '');
                    $this->filter(Studio::class, $request, $resource);
                    $resource = $resource->paginate($data['limit'] ?? 5)
                        ->appends($data);

                    $nextPageURL = $this->nextPageUrlFor($request, $resource, $type);

                    $response[$type] = [
                        'data' => StudioResourceIdentity::collection($resource),
                        'next' => empty($nextPageURL) ? null : $nextPageURL
                    ];
                    break;
                case SearchType::Users:
                    $resource = User::search($data['query'] ?? '')
                        ->query(function ($query) {
                            $query->visibleTo(auth()->user());
                        });
                    $this->filter(User::class, $request, $resource);
                    $resource = $resource->paginate($data['limit'] ?? 5)
                        ->appends($data);

                    $nextPageURL = $this->nextPageUrlFor($request, $resource, $type);

                    $response[$type] = [
                        'data' => UserResourceIdentity::collection($resource),
                        'next' => empty($nextPageURL) ? null : $nextPageURL
                    ];
                    break;
                default:
                    break;
            }
        }

        return JSONResult::success([
            'data' => $response
        ]);
    }

    /**
     * Applies filter to the search request.
     *
     * @param               $model
     * @param SearchRequest $request
     * @param Builder       $resource
     *
     * @return void
     */
    private function filter($model, SearchRequest $request, Builder $resource)
    {
        $filters = $request->input('filter');

        if (empty($filters)) {
            return;
        }

        $filters = json_decode(base64_decode($filters), true);

        if (!is_array($filters)) {
            return;
        }

        $searchFilters = $model::searchFilters();
        $expressions = [];

        foreach ($filters as $key => $value) {
            if (!in_array($this->indexedField($key), $searchFilters, true)) {
                continue;
            }

            $expression = match (true) {
                in_array($key, self::SCHEDULE_DAY_FIELDS, true) => $this->scheduleDayExpression($key, $value),
                array_key_exists($key, self::DAY_PART_FIELDS) => $this->dayPartExpression($key, $value),
                default => $this->attributeExpression($key, $value),
            };

            if (!empty($expression)) {
                $expressions[] = $expression;
            }
        }

        if (!empty($expressions)) {
            $resource->options(['filter' => implode(' AND ', $expressions)]);
        }
    }

    /**
     * Returns the indexed attribute a filter key reads.
     *
     * @param string $key
     *
     * @return string
     */
    private function indexedField(string $key): string
    {
        return self::DAY_PART_FIELDS[$key] ?? $key;
    }

    /**
     * Compiles a filter key into a Meilisearch expression.
     *
     * @param string $field
     * @param mixed  $value
     *
     * @return string|null
     */
    private function attributeExpression(string $field, mixed $value): ?string
    {
        if (!is_array($value)) {
            if (is_string($value) && str_contains($value, ',')) {
                return sprintf('%s IN [%s]', $field, $this->listValues(array_map('trim', explode(',', $value))));
            }

            return sprintf('%s = %s', $field, $this->literal($value));
        }

        $clauses = [];

        if (!empty($value['include'])) {
            $clauses[] = sprintf('%s IN [%s]', $field, $this->listValues((array) $value['include']));
        }

        if (!empty($value['exclude'])) {
            $clauses[] = sprintf('%s NOT IN [%s]', $field, $this->listValues((array) $value['exclude']));
        }

        if (isset($value['from'])) {
            $clauses[] = sprintf('%s >= %d', $field, (int) $value['from']);
        }

        if (isset($value['to'])) {
            $clauses[] = sprintf('%s <= %d', $field, (int) $value['to']);
        }

        if (empty($clauses) && array_is_list($value) && !empty($value)) {
            $clauses[] = sprintf('%s IN [%s]', $field, $this->listValues($value));
        }

        return empty($clauses) ? null : '(' . implode(' AND ', $clauses) . ')';
    }

    /**
     * Compiles a weekday filter read in the requester's timezone.
     *
     * @param string $field
     * @param mixed  $value
     *
     * @return string|null
     */
    private function scheduleDayExpression(string $field, mixed $value): ?string
    {
        $timeField = $field === 'air_day' ? 'air_time_minutes' : 'publication_time_minutes';
        $shift = $this->minutesBehindJapan();
        $clauses = [];

        foreach (['include' => false, 'exclude' => true] as $bucket => $negated) {
            $days = $this->bucketValues($value, $bucket);

            if (empty($days)) {
                continue;
            }

            $parts = array_map(function ($day) use ($field, $timeField, $shift) {
                return $this->localDayExpression($field, $timeField, (int) $day, $shift);
            }, $days);

            $clauses[] = ($negated ? 'NOT ' : '') . '(' . implode(' OR ', $parts) . ')';
        }

        return empty($clauses) ? null : '(' . implode(' AND ', $clauses) . ')';
    }

    /**
     * Compiles a single weekday into a Japanese broadcast window.
     *
     * @param string $dayField
     * @param string $timeField
     * @param int    $localDay
     * @param int    $shift
     *
     * @return string
     */
    private function localDayExpression(string $dayField, string $timeField, int $localDay, int $shift): string
    {
        $start = ((($localDay * 1440) + $shift) % 10080 + 10080) % 10080;
        $day = intdiv($start, 1440);
        $offset = $start % 1440;

        if ($offset === 0) {
            return sprintf('%s = %d', $dayField, $day);
        }

        return sprintf(
            '((%s = %d AND %s >= %d) OR (%s = %d AND %s < %d))',
            $dayField,
            $day,
            $timeField,
            $offset,
            $dayField,
            ($day + 1) % 7,
            $timeField,
            $offset
        );
    }

    /**
     * Compiles a part of day filter read in the requester's timezone.
     *
     * @param string $field
     * @param mixed  $value
     *
     * @return string|null
     */
    private function dayPartExpression(string $field, mixed $value): ?string
    {
        $timeField = self::DAY_PART_FIELDS[$field];
        $shift = $this->minutesBehindJapan();
        $clauses = [];

        foreach (['include' => false, 'exclude' => true] as $bucket => $negated) {
            $parts = $this->bucketValues($value, $bucket);

            if (empty($parts)) {
                continue;
            }

            $ranges = [];

            foreach ($parts as $part) {
                foreach ($this->dayPartRanges((int) $part, $shift) as $range) {
                    $ranges[] = sprintf('(%s >= %d AND %s < %d)', $timeField, $range[0], $timeField, $range[1]);
                }
            }

            if (empty($ranges)) {
                continue;
            }

            $clauses[] = ($negated ? 'NOT ' : '') . '(' . implode(' OR ', $ranges) . ')';
        }

        return empty($clauses) ? null : '(' . implode(' AND ', $clauses) . ')';
    }

    /**
     * Returns the Japanese minute ranges a part of day covers.
     *
     * @param int $part
     * @param int $shift
     *
     * @return array
     */
    private function dayPartRanges(int $part, int $shift): array
    {
        $bounds = self::DAY_PARTS[$part] ?? null;

        if ($bounds === null) {
            return [];
        }

        [$startHour, $endHour] = $bounds;
        $localRanges = $startHour > $endHour
            ? [[$startHour * 60, 1440], [0, $endHour * 60]]
            : [[$startHour * 60, $endHour * 60]];

        $ranges = [];

        foreach ($localRanges as [$start, $end]) {
            $shiftedStart = (($start + $shift) % 1440 + 1440) % 1440;
            $shiftedEnd = (($end + $shift) % 1440 + 1440) % 1440;

            if ($shiftedEnd === 0) {
                $shiftedEnd = 1440;
            }

            if ($shiftedStart < $shiftedEnd) {
                $ranges[] = [$shiftedStart, $shiftedEnd];
                continue;
            }

            $ranges[] = [$shiftedStart, 1440];

            if ($shiftedEnd > 0 && $shiftedEnd < 1440) {
                $ranges[] = [0, $shiftedEnd];
            }
        }

        return array_values(array_filter($ranges, fn ($range) => $range[0] < $range[1]));
    }

    /**
     * Returns the minutes Japan runs ahead of the requester.
     *
     * @return int
     */
    private function minutesBehindJapan(): int
    {
        $timezone = request()?->attributes->get('formatTimezone', 'UTC') ?? 'UTC';
        $now = now();

        try {
            $userOffset = $now->copy()->setTimezone($timezone)->utcOffset();
        } catch (Exception $exception) {
            $userOffset = 0;
        }

        return $now->copy()->setTimezone('Asia/Tokyo')->utcOffset() - $userOffset;
    }

    /**
     * Returns the values held in a filter's bucket.
     *
     * @param mixed  $value
     * @param string $bucket
     *
     * @return array
     */
    private function bucketValues(mixed $value, string $bucket): array
    {
        if (is_array($value)) {
            if (!empty($value[$bucket])) {
                return (array) $value[$bucket];
            }

            return $bucket === 'include' && array_is_list($value) ? $value : [];
        }

        return $bucket === 'include' && $value !== null ? [$value] : [];
    }

    /**
     * Returns the values written as a Meilisearch list.
     *
     * @param array $values
     *
     * @return string
     */
    private function listValues(array $values): string
    {
        return collect($values)
            ->map(fn ($value) => $this->literal($value))
            ->implode(', ');
    }

    /**
     * Returns the value written as a Meilisearch literal.
     *
     * @param mixed $value
     *
     * @return string
     */
    private function literal(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        if (filter_var($value, FILTER_VALIDATE_INT) !== false) {
            return (string) (int) $value;
        }

        return '"' . addcslashes((string) $value, '"\\') . '"';
    }

    /**
     * Returns a list of search suggestions.
     *
     * @param SearchRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function suggestions(SearchRequest $request): JsonResponse
    {
        $data = $request->validated();
        $scope = $data['scope'];
        $types = $data['types'];
        $query = $data['query'];

        if ($scope == SearchScope::Library && !auth()->check()) {
            throw new AuthenticationException('The request wasn’t accepted due to an issue with the credentials.');
        }

        $response = collect();
        foreach ($types as $type) {
            switch ($type) {
                case SearchType::Characters:
                    $resource = collect(Character::search($query)
                        ->take($data['limit'] ?? 5)
                        ->raw()['hits'])
                        ->map(function ($item) {
                            return $item['name'];
                        });
                    $response = $response->merge($resource);
                    break;
                case SearchType::Episodes:
                    $resource = collect(Episode::search($query)
                        ->take($data['limit'] ?? 5)
                        ->raw()['hits'])
                        ->map(function ($item) {
                            return $item['title'];
                        });
                    $response = $response->merge($resource);
                    break;
                case SearchType::Games:
                    if ($scope == SearchScope::Library) {
                        $resource = $this->libraryScopedSuggestions(Game::class, $data['query'] ?? '', $data['limit'] ?? 5);
                    } else {
                        $resource = collect(Game::search($query)
                            ->take($data['limit'] ?? 5)
                            ->raw()['hits'])
                            ->map(function ($item) {
                                return $item['title'];
                            })
                            ->toArray();
                    }

                    $response = $response->merge($resource);
                    break;
                case SearchType::Literatures:
                    if ($scope == SearchScope::Library) {
                        $resource = $this->libraryScopedSuggestions(Manga::class, $data['query'] ?? '', $data['limit'] ?? 5);
                    } else {
                        $resource = collect(Manga::search($query)
                            ->take($data['limit'] ?? 5)
                            ->raw()['hits'])
                            ->map(function ($item) {
                                return $item['title'];
                            })
                            ->toArray();
                    }

                    $response = $response->merge($resource);
                    break;
                case SearchType::People:
                    $resource = collect(Person::search($query)
                        ->take($data['limit'] ?? 5)
                        ->raw()['hits'])
                        ->map(function ($item) {
                            return $item['full_name'];
                        });
                    $response = $response->merge($resource);
                    break;
                case SearchType::Shows:
                    if ($scope == SearchScope::Library) {
                        $resource = $this->libraryScopedSuggestions(Anime::class, $query, $data['limit'] ?? 5);
                    } else {
                        $resource = collect(Anime::search($query)
                            ->take($data['limit'] ?? 5)
                            ->raw()['hits'])
                            ->map(function ($item) {
                                return $item['title'];
                            });
                    }

                    $response = $response->merge($resource);
                    break;
                case SearchType::Songs:
                    $resource = collect(Song::search($query)
                        ->raw()['hits'])
                        ->map(function ($item) {
                            return $item['title'];
                        });
                    $response = $response->merge($resource);
                    break;
                case SearchType::Studios:
                    $resource = collect(Studio::search($query)
                        ->take($data['limit'] ?? 5)
                        ->raw()['hits'])
                        ->map(function ($item) {
                            return $item['name'];
                        });
                    $response = $response->merge($resource);
                    break;
                case SearchType::Users:
                    $resource = collect(User::search($query)
                        ->take($data['limit'] ?? 5)
                        ->raw()['hits'])
                        ->map(function ($item) {
                            return $item['username'];
                        });
                    $response = $response->merge($resource);
                    break;
                default:
                    break;
            }
        }

        $response = $response
            ->unique(function ($item) {
                return strtolower(trim($item));
            })
            ->sort(function ($a, $b) use ($query) {
                similar_text(strtolower(trim($a)), strtolower(trim($query)), $percentA);
                similar_text(strtolower(trim($b)), strtolower(trim($query)), $percentB);
                return $percentB <=> $percentA;
            })
            ->values();

        return JSONResult::success([
            'data' => $response
        ]);
    }

    /**
     * Generate the next page url for the given resource.
     *
     * @param SearchRequest                  $request
     * @param LengthAwarePaginator|Paginator $resource
     * @param string                         $type
     *
     * @return string|null
     */
    protected function nextPageUrlFor(SearchRequest $request, LengthAwarePaginator|Paginator $resource, string $type): ?string
    {
        $nexPageUrl = $resource->nextPageUrl();

        if (empty($nexPageUrl)) {
            return null;
        }

        $uri = Uri::of($nexPageUrl)
            ->withoutQuery(['types'])
            ->withQuery([
                'types' => [
                    $type
                ],
            ]);
        $path = $uri->path();
        $query = $uri->query()->value();
        return '/' . $path . '?' . $query;
    }

    /** Searches the user's library for the given trackable class. */
    protected function libraryScopedSearch(string $modelClass, string $query, SearchRequest $request, int $limit): LengthAwarePaginator
    {
        $page = max(1, (int) $request->input('page', 1));
        $tvRating = $request->tvRating();

        $libraryIds = UserLibrary::where('user_id', auth()->id())
            ->where('trackable_type', $modelClass)
            ->pluck('trackable_id')
            ->all();

        if (empty($libraryIds)) {
            return $this->makeEmptyPaginator($request, $limit, $page);
        }

        $librarySet = array_flip($libraryIds);
        $cap = 10000;

        $search = $modelClass::search($query)->options(['attributesToRetrieve' => ['id']]);
        $this->filter($modelClass, $request, $search);
        $search->where('tv_rating_id', ['<=', $tvRating]);

        $hits = $search->take($cap)->raw()['hits'] ?? [];

        $matchedIds = [];
        foreach ($hits as $hit) {
            $id = $hit['id'] ?? null;

            if ($id !== null && isset($librarySet[$id])) {
                $matchedIds[] = $id;
            }
        }

        $total = count($matchedIds);
        $pageIds = array_slice($matchedIds, ($page - 1) * $limit, $limit);

        $items = collect();
        if (!empty($pageIds)) {
            $models = $modelClass::withoutGlobalScopes([IgnoreListScope::class])
                ->whereIn('id', $pageIds)
                ->get()
                ->keyBy('id');

            $items = collect($pageIds)
                ->map(fn ($id) => $models->get($id))
                ->filter()
                ->values();
        }

        $paginator = new ConcreteLengthAwarePaginator(
            $items,
            $total,
            $limit,
            $page,
            ['path' => $request->url()],
        );

        return $paginator->appends($request->validated());
    }

    /** Returns an empty paginator. */
    protected function makeEmptyPaginator(SearchRequest $request, int $limit, int $page): LengthAwarePaginator
    {
        $paginator = new ConcreteLengthAwarePaginator(
            [],
            0,
            $limit,
            $page,
            ['path' => $request->url()],
        );

        return $paginator->appends($request->validated());
    }

    /** Returns suggestion titles for the user's library. */
    protected function libraryScopedSuggestions(string $modelClass, string $query, int $limit): array
    {
        $libraryIds = UserLibrary::where('user_id', auth()->id())
            ->where('trackable_type', $modelClass)
            ->pluck('trackable_id')
            ->all();

        if (empty($libraryIds)) {
            return [];
        }

        $librarySet = array_flip($libraryIds);
        $cap = 10000;

        $hits = $modelClass::search($query)
            ->options(['attributesToRetrieve' => ['id', 'title']])
            ->where('tv_rating_id', ['<=', request()->tvRating()])
            ->take($cap)
            ->raw()['hits'] ?? [];

        $titles = [];
        foreach ($hits as $hit) {
            $id = $hit['id'] ?? null;
            if ($id !== null && isset($librarySet[$id])) {
                $title = $hit['title'] ?? '';
                if ($title !== '') {
                    $titles[] = $title;
                    if (count($titles) >= $limit) {
                        break;
                    }
                }
            }
        }

        return $titles;
    }
}
