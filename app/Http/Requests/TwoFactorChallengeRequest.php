<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class TwoFactorChallengeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'challenge_token' => ['bail', 'required', 'string'],
            'otp' => ['nullable', 'string', 'digits:6'],
            'recovery_code' => ['nullable', 'string', 'regex:/^[A-Za-z0-9]{10}-[A-Za-z0-9]{10}$/'],
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param Validator $validator
     *
     * @return void
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $hasOtp = !empty($this->input('otp'));
            $hasRecovery = !empty($this->input('recovery_code'));

            if (!$hasOtp && !$hasRecovery) {
                $v->errors()->add('otp', __('Either otp or recovery_code is required.'));
            }

            if ($hasOtp && $hasRecovery) {
                $v->errors()->add('otp', __('Provide either otp or recovery_code, not both.'));
            }
        });
    }
}
