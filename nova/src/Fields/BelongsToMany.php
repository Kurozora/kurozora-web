<?php

namespace Laravel\Nova\Fields;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Nova\Contracts\Deletable as DeletableContract;
use Laravel\Nova\Contracts\ListableField;
use Laravel\Nova\Contracts\PivotableField;
use Laravel\Nova\Contracts\QueryBuilder;
use Laravel\Nova\Contracts\RelatableField;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Rules\RelatableAttachment;
use Laravel\Nova\TrashedStatus;

class BelongsToMany extends Field implements DeletableContract, ListableField, PivotableField, RelatableField
{
    use Deletable,
        DetachesPivotModels,
        FormatsRelatableDisplayValues,
        ManyToManyCreationRules,
        Searchable;

    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'belongs-to-many-field';

    /**
     * The class name of the related resource.
     *
     * @var string
     */
    public $resourceClass;

    /**
     * The URI key of the related resource.
     *
     * @var string
     */
    public $resourceName;

    /**
     * The name of the Eloquent "belongs to many" relationship.
     *
     * @var string
     */
    public $manyToManyRelationship;

    /**
     * The callback that should be used to resolve the pivot fields.
     *
     * @var callable
     */
    public $fieldsCallback;

    /**
     * The callback that should be used to resolve the pivot actions.
     *
     * @var callable
     */
    public $actionsCallback;

    /**
     * The column that should be displayed for the field.
     *
     * @var \Closure
     */
    public $display;

    /**
     * The displayable name that should be used to refer to the pivot class.
     *
     * @var string
     */
    public $pivotName;

    /**
     * The displayable singular label of the relation.
     *
     * @var string
     */
    public $singularLabel;

    /**
     * Create a new field.
     *
     * @param  string  $name
     * @param  string|null  $attribute
     * @param  string|null  $resource
     * @return void
     */
    public function __construct($name, $attribute = null, $resource = null)
    {
        parent::__construct($name, $attribute);

        $resource = $resource ?? ResourceRelationshipGuesser::guessResource($name);

        $this->resourceClass = $resource;
        $this->resourceName = $resource::uriKey();
        $this->manyToManyRelationship = $this->attribute;
        $this->deleteCallback = $this->detachmentCallback();

        $this->fieldsCallback = function () {
            return [];
        };

        $this->actionsCallback = function () {
            return [];
        };

        $this->noDuplicateRelations();
    }

    /**
     * Determine if the field should be displayed for the given request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public function authorize(Request $request)
    {
        return call_user_func(
            [$this->resourceClass, 'authorizedToViewAny'], $request
        ) && parent::authorize($request);
    }

    /**
     * Resolve the field's value.
     *
     * @param  mixed  $resource
     * @param  string|null  $attribute
     * @return void
     */
    public function resolve($resource, $attribute = null)
    {
        //
    }

    /**
     * Get the validation rules for this field.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function getRules(NovaRequest $request)
    {
        $query = $this->buildAttachableQuery(
            $request, $request->{$this->attribute.'_trashed'} === 'true'
        )->toBase();

        return array_merge_recursive(parent::getRules($request), [
            $this->attribute => ['required', new RelatableAttachment($request, $query)],
        ]);
    }

    /**
     * Get the creation rules for this field.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function getCreationRules(NovaRequest $request)
    {
        return array_merge_recursive(parent::getCreationRules($request), [
            $this->attribute => array_filter($this->getManyToManyCreationRules($request)),
        ]);
    }

    /**
     * Build an attachable query for the field.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  bool  $withTrashed
     * @return \Laravel\Nova\Contracts\QueryBuilder
     */
    public function buildAttachableQuery(NovaRequest $request, $withTrashed = false)
    {
        $model = forward_static_call([$resourceClass = $this->resourceClass, 'newModel']);

        $query = app()->make(QueryBuilder::class, [$resourceClass]);

        $request->first === 'true'
                        ? $query->whereKey($model->newQueryWithoutScopes(), $request->current)
                        : $query->search(
                                $request, $model->newQuery(), $request->search,
                                [], [], TrashedStatus::fromBoolean($withTrashed)
                          );

        return $query->tap(function ($query) use ($request, $model) {
            forward_static_call($this->attachableQueryCallable($request, $model), $request, $query, $this);
        });
    }

    /**
     * Get the attachable query method name.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return array
     */
    protected function attachableQueryCallable(NovaRequest $request, $model)
    {
        return ($method = $this->attachableQueryMethod($request, $model))
                    ? [$request->resource(), $method]
                    : [$this->resourceClass, 'relatableQuery'];
    }

    /**
     * Get the attachable query method name.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return string
     */
    protected function attachableQueryMethod(NovaRequest $request, $model)
    {
        $method = 'relatable'.Str::plural(class_basename($model));

        if (method_exists($request->resource(), $method)) {
            return $method;
        }
    }

    /**
     * Format the given attachable resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  mixed  $resource
     * @return array
     */
    public function formatAttachableResource(NovaRequest $request, $resource)
    {
        return array_filter([
            'avatar' => $resource->resolveAvatarUrl($request),
            'display' => $this->formatDisplayValue($resource),
            'subtitle' => $resource->subtitle(),
            'value' => $resource->getKey(),
        ]);
    }

    /**
     * Specify the callback to be executed to retrieve the pivot fields.
     *
     * @param  callable  $callback
     * @return $this
     */
    public function fields($callback)
    {
        $this->fieldsCallback = $callback;

        return $this;
    }

    /**
     * Specify the callback to be executed to retrieve the pivot actions.
     *
     * @param  callable  $callback
     * @return $this
     */
    public function actions($callback)
    {
        $this->actionsCallback = $callback;

        return $this;
    }

    /**
     * Set the displayable name that should be used to refer to the pivot class.
     *
     * @param  string  $pivotName
     * @return $this
     */
    public function referToPivotAs($pivotName)
    {
        $this->pivotName = $pivotName;

        return $this;
    }

    /**
     * Set the displayable singular label of the resource.
     *
     * @return $this
     */
    public function singularLabel($singularLabel)
    {
        $this->singularLabel = $singularLabel;

        return $this;
    }

    /**
     * Return the validation key for the field.
     *
     * @return string
     */
    public function validationKey()
    {
        return $this->attribute != $this->resourceName
            ? $this->resourceName
            : $this->attribute;
    }

    /**
     * Prepare the field for JSON serialization.
     *
     * @return array
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return array_merge([
            'belongsToManyRelationship' => $this->manyToManyRelationship,
            'debounce' => $this->debounce,
            'listable' => true,
            'perPage'=> $this->resourceClass::$perPageViaRelationship,
            'validationKey' => $this->validationKey(),
            'resourceName' => $this->resourceName,
            'searchable' => $this->searchable,
            'withSubtitles' => $this->withSubtitles,
            'singularLabel' => $this->singularLabel ?? $this->resourceClass::singularLabel(),
        ], parent::jsonSerialize());
    }
}
