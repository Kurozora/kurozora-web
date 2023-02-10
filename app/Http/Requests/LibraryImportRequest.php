<?php

namespace App\Http\Requests;

use App\Enums\ImportBehavior;
use App\Enums\ImportService;
use App\Enums\UserLibraryKind;
use Illuminate\Foundation\Http\FormRequest;

class LibraryImportRequest extends FormRequest
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
            'library'   => ['bail', 'required', 'integer', 'in:' . implode(',', UserLibraryKind::getValues())],
            'service'   => ['bail', 'required', 'integer', 'in:' . implode(',', ImportService::getValues())],
            'behavior'  => ['bail', 'required', 'integer', 'in:' . implode(',', ImportBehavior::getValues())],
            'file'      => ['bail', 'required', 'file', 'mimes:xml', 'max:' . config('import.max_xml_file_size')],
        ];
    }
}
