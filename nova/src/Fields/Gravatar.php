<?php

namespace Laravel\Nova\Fields;

/**
 * @method static static make(mixed $name = 'Avatar', string|null $attribute = 'email')
 */
class Gravatar extends Avatar implements Unfillable
{
    /**
     * Create a new field.
     *
     * @param  string  $name
     * @param  string|null  $attribute
     * @return void
     */
    public function __construct($name = 'Avatar', $attribute = 'email')
    {
        parent::__construct($name, $attribute ?? 'email');

        $this->exceptOnForms();
    }

    /**
     * Resolve the given attribute from the given resource.
     *
     * @param  mixed  $resource
     * @param  string  $attribute
     * @return mixed
     */
    protected function resolveAttribute($resource, $attribute)
    {
        $callback = function () use ($resource, $attribute) {
            return 'https://www.gravatar.com/avatar/'.md5(strtolower(parent::resolveAttribute($resource, $attribute))).'?s=300';
        };

        $this->preview($callback)->thumbnail($callback);
    }

    /**
     * Prepare the field for JSON serialization.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_merge([
            'indexName' => '',
        ], parent::jsonSerialize());
    }
}
