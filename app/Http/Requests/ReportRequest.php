<?php

namespace App\Http\Requests;

use App\Enums\ParentalGuideReportReason;
use BenSampo\Enum\Rules\EnumValue;
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
            'reason_key' => ['bail', 'required', 'string', new EnumValue(ParentalGuideReportReason::class, false)],
            'details' => ['bail', 'nullable', 'string', 'max:1000', 'required_if:reason_key,other'],
        ];
    }
}
