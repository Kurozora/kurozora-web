<?php

namespace App\Http\Requests;

use App\Genre;
use Illuminate\Foundation\Http\FormRequest;

class GetExplorePageRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'genre_id' => ['numeric', 'exists:' . Genre::TABLE_NAME . ',id']
        ];
    }
}
