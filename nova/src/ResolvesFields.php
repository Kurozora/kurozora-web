<?php

namespace Laravel\Nova;

use Closure;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Laravel\Nova\Actions\Actionable;
use Laravel\Nova\Contracts\Cover;
use Laravel\Nova\Contracts\ListableField;
use Laravel\Nova\Contracts\Resolvable;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\FieldCollection;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphMany;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\MorphToMany;
use Laravel\Nova\Http\Requests\NovaRequest;

trait ResolvesFields
{
    /**
     * Resolve the index fields.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return \Laravel\Nova\Fields\FieldCollection
     */
    public function indexFields(NovaRequest $request)
    {
        return $this->resolveFields($request, function (FieldCollection $fields) use ($request) {
            return $fields->reject(function ($field) use ($request) {
                return $field instanceof ListableField || ! $field->isShownOnIndex($request, $this->resource);
            });
        })->each(function ($field) use ($request) {
            if ($field instanceof Resolvable && ! $field->pivot) {
                $field->resolveForDisplay($this->resource);
            }

            if ($field instanceof Resolvable && $field->pivot) {
                $accessor = $this->pivotAccessorFor($request, $request->viaResource);

                $field->resolveForDisplay($this->{$accessor} ?? new Pivot);
            }
        });
    }

    /**
     * Resolve the detail fields.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return \Laravel\Nova\Fields\FieldCollection
     */
    public function detailFields(NovaRequest $request)
    {
        return $this->resolveFields($request, function (FieldCollection $fields) use ($request) {
            return $fields->filter->isShownOnDetail($request, $this->resource);
        })->when($this->shouldAddActionsField($request), function ($fields) {
            return $fields->push($this->actionfield());
        })->each(function ($field) use ($request) {
            if ($field instanceof ListableField || ! $field instanceof Resolvable) {
                return;
            }

            if ($field->pivot) {
                $accessor = $this->pivotAccessorFor($request, $request->viaResource);

                $field->resolveForDisplay($this->{$accessor} ?? new Pivot);
            } else {
                $field->resolveForDisplay($this->resource);
            }
        });
    }

    /**
     * Determine if the resource should have an Action field.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return bool
     */
    protected function shouldAddActionsField($request)
    {
        return with($this->actionfield(), function ($actionField) use ($request) {
            return in_array(Actionable::class, class_uses_recursive(static::newModel())) && $actionField->authorizedToSee($request);
        });
    }

    /**
     * Return a new Action field instance.
     *
     * @return \Laravel\Nova\Fields\MorphMany
     */
    protected function actionfield()
    {
        return MorphMany::make(__('Actions'), 'actions', Nova::actionResource())
            ->canSee(function ($request) {
                return Nova::actionResource()::authorizedToViewAny($request);
            });
    }

    /**
     * Resolve the detail fields and assign them to their associated panel.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return \Laravel\Nova\Fields\FieldCollection
     */
    public function detailFieldsWithinPanels(NovaRequest $request)
    {
        return $this->assignToPanels(
            Panel::defaultNameForDetail($request->newResource()),
            $this->detailFields($request)
        );
    }

    /**
     * Resolve the creation fields.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return \Laravel\Nova\Fields\FieldCollection
     */
    public function creationFields(NovaRequest $request)
    {
        return $this->resolveFields($request, function ($fields) use ($request) {
            return $this->removeNonCreationFields($request, $fields);
        });
    }

    /**
     * Return the creation fields excluding any readonly ones.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return \Laravel\Nova\Fields\FieldCollection
     */
    public function creationFieldsWithoutReadonly(NovaRequest $request)
    {
        return $this->creationFields($request)
                    ->reject(function ($field) use ($request) {
                        return $field->isReadonly($request);
                    });
    }

    /**
     * Resolve the creation fields and assign them to their associated panel.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return \Laravel\Nova\Fields\FieldCollection
     */
    public function creationFieldsWithinPanels(NovaRequest $request)
    {
        return $this->assignToPanels(
            Panel::defaultNameForCreate($request->newResource()),
            $this->creationFields($request)
        );
    }

    /**
     * Resolve the creation pivot fields for a related resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  string  $relatedResource
     * @return \Laravel\Nova\Fields\FieldCollection
     */
    public function creationPivotFields(NovaRequest $request, $relatedResource)
    {
        return $this->removeNonCreationFields(
            $request, $this->resolvePivotFields($request, $relatedResource)
        );
    }

    /**
     * Remove non-creation fields from the given collection.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Laravel\Nova\Fields\FieldCollection  $fields
     * @return \Laravel\Nova\Fields\FieldCollection
     */
    protected function removeNonCreationFields(NovaRequest $request, FieldCollection $fields)
    {
        return $fields->reject(function ($field) use ($request) {
            return $field instanceof ListableField ||
                   $field instanceof ResourceToolElement ||
                   $field->attribute === 'ComputedField' ||
                   ($field instanceof ID && $field->attribute === $this->resource->getKeyName()) ||
                   ! $field->isShownOnCreation($request);
        });
    }

    /**
     * Resolve the update fields.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return \Laravel\Nova\Fields\FieldCollection
     */
    public function updateFields(NovaRequest $request)
    {
        return $this->resolveFields($request, function ($fields) use ($request) {
            return $this->removeNonUpdateFields($request, $fields);
        });
    }

    /**
     * Return the update fields excluding any readonly ones.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return \Laravel\Nova\Fields\FieldCollection
     */
    public function updateFieldsWithoutReadonly(NovaRequest $request)
    {
        return $this->updateFields($request)
                    ->reject(function ($field) use ($request) {
                        return $field->isReadonly($request);
                    });
    }

    /**
     * Resolve the update fields and assign them to their associated panel.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return \Laravel\Nova\Fields\FieldCollection
     */
    public function updateFieldsWithinPanels(NovaRequest $request)
    {
        return $this->assignToPanels(
            Panel::defaultNameForUpdate($request->newResource()),
            $this->updateFields($request)
        );
    }

    /**
     * Resolve the update pivot fields for a related resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  string  $relatedResource
     * @return \Laravel\Nova\Fields\FieldCollection
     */
    public function updatePivotFields(NovaRequest $request, $relatedResource)
    {
        return $this->removeNonUpdateFields(
            $request, $this->resolvePivotFields($request, $relatedResource)
        );
    }

    /**
     * Remove non-update fields from the given collection.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Laravel\Nova\Fields\FieldCollection  $fields
     * @return \Laravel\Nova\Fields\FieldCollection
     */
    protected function removeNonUpdateFields(NovaRequest $request, FieldCollection $fields)
    {
        return $fields->reject(function ($field) use ($request) {
            return $field instanceof ListableField ||
                   $field instanceof ResourceToolElement ||
                   $field->attribute === 'ComputedField' ||
                   ($field instanceof ID && $field->attribute === $this->resource->getKeyName()) ||
                   ! $field->isShownOnUpdate($request, $this->resource);
        });
    }

    /**
     * Resolve the given fields to their values.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Closure|null  $filter
     * @return \Laravel\Nova\Fields\FieldCollection
     */
    protected function resolveFields(NovaRequest $request, Closure $filter = null)
    {
        $fields = $this->availableFields($request);

        if (! is_null($filter)) {
            $fields = $filter($fields);
        }

        $fields->whereInstanceOf(Resolvable::class)->each->resolve($this->resource);

        $fields = $fields->filter->authorize($request)->values();

        return $request->viaRelationship()
                    ? $this->withPivotFields($request, $fields->all())
                    : $fields;
    }

    /**
     * Resolve the field for the given attribute.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  string  $attribute
     * @return \Laravel\Nova\Fields\Field
     */
    public function resolveFieldForAttribute(NovaRequest $request, $attribute)
    {
        return $this->resolveFields($request)->findFieldByAttribute($attribute);
    }

    /**
     * Resolve the inverse field for the given relationship attribute.
     *
     * This is primarily used for Relatable rule to check if has-one / morph-one relationships are "full".
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  string  $attribute
     * @param  string|null  $morphType
     * @return \Laravel\Nova\Fields\FieldCollection
     */
    public function resolveInverseFieldsForAttribute(NovaRequest $request, $attribute, $morphType = null)
    {
        $field = $this->availableFields($request)
                      ->filter->authorize($request)
                      ->findFieldByAttribute($attribute);

        if (! isset($field->resourceClass)) {
            return new FieldCollection();
        }

        $relatedResource = $field instanceof MorphTo
                                ? Nova::resourceForKey($morphType ?? $request->{$attribute.'_type'})
                                : ($field->resourceClass ?? null);

        $relatedResource = new $relatedResource($relatedResource::newModel());

        $result = $relatedResource->availableFields($request)->reject(function ($f) use ($field) {
            return isset($f->attribute) &&
                   isset($field->inverse) &&
                   $f->attribute !== $field->inverse;
        })->filter(function ($field) use ($request) {
            return isset($field->resourceClass) &&
                   $field->resourceClass == $request->resource();
        });

        return $result;
    }

    /**
     * Resolve the resource's avatar field.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return \Laravel\Nova\Contracts\Cover|null
     */
    public function resolveAvatarField(NovaRequest $request)
    {
        return with($this->availableFields($request)
            ->filter->authorize($request)
            ->whereInstanceOf(Cover::class)
            ->first(),
            function ($field) {
                if ($field instanceof Resolvable) {
                    $field->resolve($this->resource);
                }

                return $field;
            }
        );
    }

    /**
     * Resolve the resource's avatar URL, if applicable.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return string|null
     */
    public function resolveAvatarUrl(NovaRequest $request)
    {
        $field = $this->resolveAvatarField($request);

        if ($field) {
            return $field->resolveThumbnailUrl();
        }
    }

    /**
     * Determine whether the resource's avatar should be rounded, if applicable.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     * @return bool
     */
    public function resolveIfAvatarShouldBeRounded(NovaRequest $request)
    {
        $field = $this->resolveAvatarField($request);

        if ($field) {
            return $field->isRounded();
        }

        return false;
    }

    /**
     * Get the panels that are available for the given create request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return \Illuminate\Support\Collection
     */
    public function availablePanelsForCreate($request)
    {
        return $this->panelsWithDefaultLabel(Panel::defaultNameForCreate($request->newResource()), $request);
    }

    /**
     * Get the panels that are available for the given update request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return \Illuminate\Support\Collection
     */
    public function availablePanelsForUpdate($request)
    {
        return $this->panelsWithDefaultLabel(Panel::defaultNameForUpdate($request->newResource()), $request);
    }

    /**
     * Get the panels that are available for the given detail request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return \Illuminate\Support\Collection
     */
    public function availablePanelsForDetail($request)
    {
        return $this->panelsWithDefaultLabel(Panel::defaultNameForDetail($request->newResource()), $request);
    }

    /**
     * Get the fields that are available for the given request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return \Laravel\Nova\Fields\FieldCollection
     */
    public function availableFields(NovaRequest $request)
    {
        return new FieldCollection(array_values($this->filter($this->fields($request))));
    }

    /**
     * Merge the available pivot fields with the given fields.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  array  $fields
     * @return \Laravel\Nova\Fields\FieldCollection
     */
    protected function withPivotFields(NovaRequest $request, array $fields)
    {
        $pivotFields = $this->resolvePivotFields($request, $request->viaResource)->all();

        if ($index = $this->indexToInsertPivotFields($request, $fields)) {
            array_splice($fields, $index + 1, 0, $pivotFields);
        } else {
            $fields = array_merge($fields, $pivotFields);
        }

        return new FieldCollection($fields);
    }

    /**
     * Resolve the pivot fields for the requested resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  string  $relatedResource
     * @return \Laravel\Nova\Fields\FieldCollection
     */
    public function resolvePivotFields(NovaRequest $request, $relatedResource)
    {
        $fields = $this->pivotFieldsFor($request, $relatedResource);

        return FieldCollection::make($this->filter($fields->each(function ($field) use ($request, $relatedResource) {
            if ($field instanceof Resolvable) {
                $accessor = $this->pivotAccessorFor($request, $relatedResource);

                $field->resolve($this->{$accessor} ?? new Pivot);
            }
        })->filter->authorize($request)->values()->all()))->values();
    }

    /**
     * Get the pivot fields for the resource and relation.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  string  $relatedResource
     * @return \Laravel\Nova\Fields\FieldCollection
     */
    protected function pivotFieldsFor(NovaRequest $request, $relatedResource)
    {
        $field = $this->availableFields($request)->first(function ($field) use ($relatedResource) {
            return isset($field->resourceName) &&
                   $field->resourceName == $relatedResource &&
                   ($field instanceof BelongsToMany || $field instanceof MorphToMany);
        });

        if ($field && isset($field->fieldsCallback)) {
            return FieldCollection::make(array_values(
                $this->filter(call_user_func($field->fieldsCallback, $request, $this->resource))
            ))->each(function ($field) {
                $field->pivot = true;
            });
        }

        return new FieldCollection();
    }

    /**
     * Get the name of the pivot accessor for the requested relationship.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  string  $relatedResource
     * @return string
     */
    public function pivotAccessorFor(NovaRequest $request, $relatedResource)
    {
        $field = $this->availableFields($request)->first(function ($field) use ($request, $relatedResource) {
            return ($field instanceof BelongsToMany ||
                    $field instanceof MorphToMany) &&
                   $field->resourceName == $relatedResource;
        });

        return $this->resource->{$field->manyToManyRelationship}()->getPivotAccessor();
    }

    /**
     * Get the index where the pivot fields should be spliced into the field array.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  array  $fields
     * @return int
     */
    protected function indexToInsertPivotFields(NovaRequest $request, array $fields)
    {
        foreach ($fields as $index => $field) {
            if (isset($field->resourceName) &&
                $field->resourceName == $request->viaResource) {
                return $index;
            }
        }
    }

    /**
     * Get the displayable pivot model name from a field.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  string  $field
     * @return string|null
     */
    public function pivotNameForField(NovaRequest $request, $field)
    {
        $field = $this->availableFields($request)->findFieldByAttribute($field);

        if (! $field || (! $field instanceof BelongsToMany &&
                         ! $field instanceof MorphToMany)) {
            return self::DEFAULT_PIVOT_NAME;
        }

        if (isset($field->pivotName)) {
            return $field->pivotName;
        }
    }

    /**
     * Return the panels for this request with the default label.
     *
     * @param  string  $label
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return \Illuminate\Support\Collection
     */
    protected function panelsWithDefaultLabel($label, NovaRequest $request)
    {
        return with(
            collect(array_values($this->fields($request)))->whereInstanceOf(Panel::class)->values(),
            function ($panels) use ($label) {
                return $panels->when($panels->where('name', $label)->isEmpty(), function ($panels) use ($label) {
                    return $panels->prepend((new Panel($label))->withToolbar());
                })->all();
            }
        );
    }

    /**
     * Assign the fields with the given panels to their parent panel.
     *
     * @param  string  $label
     * @param  \Laravel\Nova\Fields\FieldCollection  $fields
     * @return \Laravel\Nova\Fields\FieldCollection
     */
    protected function assignToPanels($label, FieldCollection $fields)
    {
        return $fields->map(function ($field) use ($label) {
            if (! $field->panel) {
                $field->panel = $label;
            }

            return $field;
        });
    }
}
