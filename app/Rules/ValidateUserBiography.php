<?php

namespace App\Rules;

use App\Models\User;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class ValidateUserBiography implements Rule
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
        $validator = Validator::make([$attribute => $value], [
            $attribute => ['min:0', 'max:' . User::BIOGRAPHY_LIMIT],
        ]);

        if ($validator->fails()) {
            $this->error = $validator->errors()->first();
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
