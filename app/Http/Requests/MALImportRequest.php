<?php

namespace App\Http\Requests;

use App\Enums\MALImportBehavior;
use Illuminate\Foundation\Http\FormRequest;

class MALImportRequest extends FormRequest
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
            'file'      => ['required', 'file', 'mimes:xml', 'max:' . config('mal-import.max_xml_file_size')],
            'behavior'  => ['required', 'integer', 'in:' . implode(',', MALImportBehavior::getValues())]
        ];
    }
}
