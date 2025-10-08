<?php

namespace App\Http\Requests;

use App\Enums\BrowseSeasonKind;
use App\Models\MediaType;
use Illuminate\Foundation\Http\FormRequest;

class GetBrowseSeasonRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'kind' => ['bail', 'nullable', 'integer', 'in:' . implode(',', BrowseSeasonKind::getValues())],
            'mediaTypes' => ['bail', 'nullable', 'array'],
            'mediaTypes.*' => ['bail', 'integer', 'exists:' . MediaType::TABLE_NAME . ',id'],
        ];
    }
}
