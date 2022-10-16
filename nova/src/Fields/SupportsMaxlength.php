<?php

namespace Laravel\Nova\Fields;

trait SupportsMaxlength
{
    /**
     * Set a maxlength value for the field.
     *
     * @param  int  $value
     * @param  bool  $enforce
     * @return $this
     */
    public function maxlength($value, $enforce = false)
    {
        $this->withMeta(['maxlength' => $value]);

        if ($enforce) {
            $this->enforceMaxlength();
        }

        return $this;
    }

    /**
     * Indicate that maxlength should be enforced on the field.
     *
     * @return $this
     */
    public function enforceMaxlength()
    {
        $this->withMeta(['enforceMaxlength' => true]);

        return $this;
    }
}
