<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class ValidateAvatarImage implements Rule
{
    /** @var string $error */
    protected string $error;

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        $imgValidator = Validator::make([$attribute => $value], [
            $attribute => 'mimes:jpeg,jpg,png|max:1000|nullable',
        ]);

        if($imgValidator->fails()) {
            $this->error = $imgValidator->errors()->first();
            return false;
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return $this->error;
    }
}
