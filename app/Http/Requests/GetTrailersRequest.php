<?php

namespace App\Http\Requests;

use App\Enums\TrailerSort;
use BenSampo\Enum\Rules\EnumValue;

class GetTrailersRequest extends GetPaginatedRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'sort' => ['bail', 'nullable', 'integer', new EnumValue(TrailerSort::class, false)],
        ]);
    }
}
