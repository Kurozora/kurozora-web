<?php

namespace Laravel\Nova\Fields;

use Laravel\Nova\Contracts\ListableField;
use Laravel\Nova\Contracts\RelatableField;

class HasManyThrough extends HasMany implements ListableField, RelatableField
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'has-many-through-field';

    /**
     * The name of the Eloquent "has many through" relationship.
     *
     * @var string
     */
    public $hasManyThroughRelationship;

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
        parent::__construct($name, $attribute, $resource);

        $this->hasManyThroughRelationship = $this->attribute;
    }

    /**
     * Prepare the field for JSON serialization.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return array_merge([
            'hasManyThroughRelationship' => $this->hasManyThroughRelationship,
            'listable' => true,
            'perPage'=> $this->resourceClass::$perPageViaRelationship,
            'resourceName' => $this->resourceName,
            'singularLabel' => $this->singularLabel ?? $this->resourceClass::singularLabel(),
        ], parent::jsonSerialize());
    }
}
