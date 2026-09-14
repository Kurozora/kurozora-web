<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetLibrarySyncRequest extends FormRequest
{
    /**
     * The cursored delta streams a sync round carries.
     *
     * @var array
     */
    public const array CURSORED_STREAMS = ['entries', 'shows', 'literatures', 'games'];

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
        $rules = [
            'since' => ['nullable', 'array'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:10000'],
        ];

        foreach (self::CURSORED_STREAMS as $stream) {
            $rules['since.' . $stream] = ['nullable', 'array'];
            $rules['since.' . $stream . '.updated_at'] = ['nullable', 'date'];
            $rules['since.' . $stream . '.id'] = ['nullable', 'string'];
            $rules['since.' . $stream . '.synced_at'] = ['nullable', 'integer'];
        }

        return $rules;
    }
}
