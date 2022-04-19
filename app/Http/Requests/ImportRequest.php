<?php

namespace App\Http\Requests;

use App\Enums\ImportBehavior;
use App\Enums\ImportService;
use Illuminate\Foundation\Http\FormRequest;

class ImportRequest extends FormRequest
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
            'service'   => ['nullable', 'integer', 'in:' . implode(',', ImportService::getValues())],
            'file'      => ['required', 'file', 'mimes:xml', 'max:' . config('import.max_xml_file_size')],
            'behavior'  => ['required', 'integer', 'in:' . implode(',', ImportBehavior::getValues())],
        ];
    }
}
