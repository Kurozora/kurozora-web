<?php

namespace App\Http\Requests;

use App\Enums\ImportBehavior;
use App\Enums\ImportService;
use App\Enums\UserLibraryKind;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
     * Normalizes the deprecated `library` alias onto the canonical `kind` param.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        if (!$this->has('kind') && $this->has('library')) {
            $this->merge(['kind' => $this->input('library')]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $kindRule = 'in:' . implode(',', UserLibraryKind::getValues());
        $importService = ImportService::coerce((int) $this->input('service'));

        return [
            'kind'      => ['bail', Rule::requiredIf(!$importService?->canInferLibraryKind()), 'nullable', 'integer', $kindRule],
            'library'   => ['bail', 'nullable', 'integer', $kindRule],
            'service'   => ['bail', 'required', 'integer', 'in:' . implode(',', ImportService::getValues())],
            'behavior'  => ['bail', 'required', 'integer', 'in:' . implode(',', ImportBehavior::getValues())],
            'file'      => ['bail', 'required', 'file', 'mimes:xml,gz,zip', 'max:' . config('import.max_xml_file_size')],
        ];
    }
}
