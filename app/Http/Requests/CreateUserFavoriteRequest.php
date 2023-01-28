<?php

namespace App\Http\Requests;

use App\Enums\UserLibraryType;
use App\Models\Anime;
use Illuminate\Foundation\Http\FormRequest;

class CreateUserFavoriteRequest extends FormRequest
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
            'anime_id'  => ['bail', 'required_without:model_id,library', 'integer', 'exists:' . Anime::TABLE_NAME . ',id'],
            'library'   => ['bail', 'required_without:anime_id', 'integer', 'in:' . implode(',', UserLibraryType::getValues())],
            'model_id'  => ['bail', 'required_without:anime_id', 'string'],
        ];
    }
}
