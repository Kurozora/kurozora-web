<?php

namespace App\Http\Requests;

use App\Enums\UserLibraryKind;
use Illuminate\Foundation\Http\FormRequest;

class ClearUserLibraryRequest extends FormRequest
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
     * Normalises the deprecated `library` alias onto the canonical `kind` param.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        if (!$this->has('kind') && $this->has('library')) {
            $this->merge(['kind' => $this->input('library')]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $kindRule = 'in:' . implode(',', UserLibraryKind::getValues());

        return [
            'password' => ['required'],
            'kind'    => ['bail', 'required_without:anime_id', 'integer', $kindRule],
            'library' => ['bail', 'nullable', 'integer', $kindRule],
        ];
    }
}
