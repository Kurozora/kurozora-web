<?php

namespace App\Http\Requests;

use App\Contracts\Reportable;
use Illuminate\Foundation\Http\FormRequest;

class ReportRequest extends FormRequest
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
            'reason_key' => ['bail', 'required', 'string', 'in:' . implode(',', $this->allowedReasonKeys())],
            'details' => ['bail', 'nullable', 'string', 'max:1000', 'required_if:reason_key,other'],
        ];
    }

    /**
     * Resolves the list of valid reason keys for the bound reportable model.
     *
     * @return array<int, string>
     */
    protected function allowedReasonKeys(): array
    {
        foreach ($this->route()?->parameters() ?? [] as $parameter) {
            if ($parameter instanceof Reportable) {
                return $parameter::availableReportReasons();
            }
        }

        return ['other'];
    }
}
