<?php

namespace App\Http\Requests;

use App\Enums\FavoriteKind;
use Illuminate\Foundation\Http\FormRequest;

class GetUserFavoritesRequest extends FormRequest
{
    /**
     * The maximum number of IDs accepted on the overlay branch.
     */
    const int MAX_IDS = 50;

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
     * Normalises the deprecated `library` alias and splits comma-joined `ids`.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        if ($this->ids && is_string($this->ids)) {
            $this->merge(['ids' => explode(',', $this->ids)]);
        }

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
        $kindRule = 'in:' . implode(',', FavoriteKind::getValues());

        return [
            'kind' => ['bail', 'nullable', 'integer', $kindRule],
            'library' => ['bail', 'nullable', 'integer', $kindRule],
            'limit' => ['bail', 'integer', 'min:1', 'max:100'],
            'ids' => ['bail', 'nullable', 'array', 'max:' . self::MAX_IDS],
            'ids.*' => ['bail', 'string'],
        ];
    }
}
