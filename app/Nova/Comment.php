<?php

namespace App\Nova;

use App\Nova\Filters\IsLocked;
use App\Nova\Filters\IsNsfl;
use App\Nova\Filters\IsNsfw;
use App\Nova\Filters\IsSpoiler;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

class Comment extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\Comment::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'content'
    ];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Comment';

    /**
     * Get the fields displayed by the resource.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function fields(NovaRequest $request): array
    {
        return [
            Heading::make('Identification'),

            ID::make()->sortable(),

            BelongsTo::make('User')
                ->searchable()
                ->sortable(),

            Heading::make('Meta information'),

            MorphTo::make('Commentable')
                ->types([
                    Anime::class,
                    Episode::class,
                    Manga::class,
                    Game::class,
                ])
                ->searchable()
                ->sortable(),

            Text::make('Model Type')
                ->onlyOnIndex()
                ->onlyOnDetail()
                ->sortable(),

            Textarea::make('Content')
                ->rules('required')
                ->help('The content of the comment.'),

            Text::make('Content')
                ->displayUsing(function ($value) {
                    return str($value)->limit(50);
                })
                ->onlyOnIndex(),

            Boolean::make('Is Spoiler')
                ->rules('required')
                ->sortable()
                ->help('The comment contains spoiler.'),

            Boolean::make('Is NSFW')
                ->rules('required')
                ->sortable()
                ->help('The comment is Not Safe For Work.'),

            Boolean::make('Is NSFL')
                ->rules('required')
                ->sortable()
                ->help('The comment is Not Safe For Looking.'),

            Boolean::make('Is Locked')
                ->rules('required')
                ->sortable()
                ->help('The comment is locked, so no further interaction is possible.'),

            Heading::make('Statistics'),

            Number::make('Replies Count')
                ->default(0)
                ->help('The number of replies.'),

            Number::make('Likes Count')
                ->default(0)
                ->help('The number of likes.'),

            Number::make('Reports Count')
                ->default(0)
                ->help('The number of reports.'),

            HasMany::make('Replies', 'replies', Comment::class),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function cards(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function filters(NovaRequest $request): array
    {
        return [
            new IsSpoiler,
            new IsNsfw,
            new IsNsfl,
            new IsLocked,
        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function lenses(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function actions(NovaRequest $request): array
    {
        return [];
    }
}
