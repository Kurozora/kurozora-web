<?php

namespace App\Http\Requests;

use App\Enums\UserLibraryKind;
use Illuminate\Foundation\Http\FormRequest;

class GetLibrarySyncRequest extends FormRequest
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
            'kind' => ['required', 'integer', $kindRule],
            'library' => ['nullable', 'integer', $kindRule],

            // Cursor-based sync
            'since.updated_at' => ['nullable', 'date'],
            'since.id' => ['nullable', 'string'],
            'since.synced_at' => ['nullable', 'integer'],

            'limit' => ['nullable', 'integer', 'min:1', 'max:10000'],
        ];
    }
}
