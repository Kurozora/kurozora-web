<?php

namespace App\Nova;

use App\Enums\ParentalGuideReportReason;
use App\Enums\ReportReason;
use App\Models\Report as ReportModel;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Card;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Filters\Filter;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Lenses\Lens;

class Report extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<ReportModel>
     */
    public static string $model = ReportModel::class;

    /**
     * The underlying model resource instance.
     *
     * @var ReportModel|null
     */
    public $resource;

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
        'id', 'details'
    ];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Reports';

    /**
     * Get the fields displayed by the resource.
     *
     * @return array<int, Field>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            Heading::make('Identification')
                ->onlyOnDetail(),

            ID::make()->sortable(),

            BelongsTo::make('Reported By', 'user', User::class)
                ->searchable()
                ->sortable()
                ->help('The user who filed this report.'),

            MorphTo::make('Reportable')
                ->types([
                    MediaRating::class,
                    FeedMessage::class,
                    ParentalGuideEntry::class,
                ])
                ->searchable()
                ->sortable(),

            Heading::make('Meta information'),

            Badge::make('Reason', 'reason_key')
                ->map(static::reasonBadgeTypes())
                ->labels(static::reasonLabels())
                ->withIcons()
                ->filterable()
                ->sortable(),

            Textarea::make('Details')
                ->help('What the reporter told us.'),

            Text::make('Details')
                ->displayUsing(function ($details) {
                    return str($details)->limit(50);
                })
                ->onlyOnIndex(),

            DateTime::make('Created At')
                ->exceptOnForms()
                ->sortable(),
        ];
    }

    /**
     * Apply the default orderings for the given resource.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public static function defaultOrderings($query): Builder
    {
        return $query->orderByRaw('reason_key = ? desc', [ReportReason::SelfHarm])
            ->orderByDesc('created_at');
    }

    /**
     * The badge type of every reason a report can carry.
     *
     * @return array<string, string>
     */
    protected static function reasonBadgeTypes(): array
    {
        return [
            ReportReason::SelfHarm => 'danger',
            ReportReason::Abuse => 'warning',
            ReportReason::Inappropriate => 'warning',
            ReportReason::Piracy => 'warning',
            ReportReason::Spam => 'info',
            ReportReason::Spoiler => 'info',
            ReportReason::NotAReview => 'info',
            ReportReason::Other => 'info',
            ParentalGuideReportReason::Inaccurate => 'info',
        ];
    }

    /**
     * The localized label of every reason a report can carry.
     *
     * @return array<string, string>
     */
    protected static function reasonLabels(): array
    {
        return collect(array_keys(static::reasonBadgeTypes()))
            ->mapWithKeys(fn (string $reason) => [
                $reason => ReportReason::hasValue($reason)
                    ? ReportReason::getDescription($reason)
                    : ParentalGuideReportReason::getDescription($reason),
            ])
            ->all();
    }

    /**
     * Get the cards available for the resource.
     *
     * @return array<int, Card>
     */
    public function cards(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @return array<int, Filter>
     */
    public function filters(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @return array<int, Lens>
     */
    public function lenses(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @return array<int, Action>
     */
    public function actions(NovaRequest $request): array
    {
        return [];
    }
}
