<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSongAppleMusicIDRequest extends FormRequest
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
            'am_id' => [
                'bail',
                'required',
                'integer',
                'min:1',
                Rule::unique('songs', 'am_id')
                    ->ignore($this->route('song'))
                    ->whereNull('deleted_at'),
            ],
        ];
    }
}
