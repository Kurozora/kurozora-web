<?php

namespace App\Http\Requests;

use App\Enums\UserLibraryKind;
use App\Models\Anime;
use App\Rules\ValidateAnimeIDIsTracked;
use Illuminate\Foundation\Http\FormRequest;

class AddReminderRequest extends FormRequest
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
            'anime_id' => ['bail', 'required_without:model_id,library', 'integer', 'exists:' . Anime::TABLE_NAME . ',id', new ValidateAnimeIDIsTracked],
            'library' => ['bail', 'required_without:anime_id', 'integer', 'in:' . implode(',', UserLibraryKind::getValues())],
            'model_id' => ['bail', 'required_without:anime_id', 'string'],
        ];
    }
}
