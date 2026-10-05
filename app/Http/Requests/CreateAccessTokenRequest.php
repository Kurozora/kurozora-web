<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateAccessTokenRequest extends FormRequest
{
    /**
     * The abilities a user-issued token may carry.
     */
    const array ALLOWED_ABILITIES = [
        'scrobble',
    ];

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
            'name' => ['bail', 'required', 'string', 'max:255'],
            'abilities' => ['bail', 'nullable', 'array'],
            'abilities.*' => ['bail', 'string', 'in:' . implode(',', self::ALLOWED_ABILITIES)],
            'platform' => ['bail', 'nullable', 'string', 'max:255'],
            'platform_version' => ['bail', 'nullable', 'string', 'max:255'],
            'device_vendor' => ['bail', 'nullable', 'string', 'max:255'],
            'device_model' => ['bail', 'nullable', 'string', 'max:255'],
        ];
    }
}
