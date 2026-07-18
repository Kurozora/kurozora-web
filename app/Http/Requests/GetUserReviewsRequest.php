<?php

namespace App\Http\Requests;

use App\Enums\ReviewKind;
use Illuminate\Foundation\Http\FormRequest;

class GetUserReviewsRequest extends FormRequest
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
     * Splits comma-joined `ids` into an array prior to validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        if ($this->ids && is_string($this->ids)) {
            $this->merge(['ids' => explode(',', $this->ids)]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $kindRule = 'in:' . implode(',', ReviewKind::getValues());

        return [
            'kind' => ['bail', $this->filled('ids') ? 'required' : 'nullable', 'integer', $kindRule],
            'ids' => ['bail', 'nullable', 'array', 'max:' . self::MAX_IDS],
            'ids.*' => ['bail', 'string'],
            'limit' => ['bail', 'integer', 'min:1', 'max:100'],
        ];
    }
}
