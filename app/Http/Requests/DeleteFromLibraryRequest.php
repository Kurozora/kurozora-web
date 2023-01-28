<?php

namespace App\Http\Requests;

use App\Enums\UserLibraryType;
use App\Models\Anime;
use App\Rules\ValidateAnimeIDIsTracked;
use Illuminate\Foundation\Http\FormRequest;

class DeleteFromLibraryRequest extends FormRequest
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
            'anime_id'  => ['bail', 'required_without:item_id,library', 'integer', 'exists:' . Anime::TABLE_NAME . ',id', new ValidateAnimeIDIsTracked],
            'library'   => ['bail', 'required_without:anime_id', 'integer', 'in:' . implode(',', UserLibraryType::getValues())],
            'item_id'   => ['bail', 'required_without:anime_id', 'string']
        ];
    }
}
