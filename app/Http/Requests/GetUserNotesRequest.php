<?php

namespace App\Http\Requests;

use App\Enums\ReviewKind;
use Illuminate\Foundation\Http\FormRequest;

class GetUserNotesRequest extends FormRequest
{
    /**
     * The maximum number of IDs accepted.
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
     * Splits comma-joined `ids`.
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
        return [
            'kind' => ['bail', 'required', 'integer', 'in:' . implode(',', ReviewKind::getValues())],
            'ids' => ['bail', 'required', 'array', 'min:1', 'max:' . self::MAX_IDS],
            'ids.*' => ['bail', 'string'],
        ];
    }
}
