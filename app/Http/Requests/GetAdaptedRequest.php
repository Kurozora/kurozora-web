<?php

namespace App\Http\Requests;

use App\Enums\AdaptedAnimeFilter;
use BenSampo\Enum\Rules\EnumValue;

class GetAdaptedRequest extends GetPaginatedRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'filter' => ['bail', 'nullable', 'integer', new EnumValue(AdaptedAnimeFilter::class, false)],
        ]);
    }
}
