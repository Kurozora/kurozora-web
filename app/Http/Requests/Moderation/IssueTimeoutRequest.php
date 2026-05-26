<?php

namespace App\Http\Requests\Moderation;

use App\Enums\TimeoutDuration;
use App\Enums\TimeoutReason;
use BenSampo\Enum\Rules\EnumValue;
use Illuminate\Foundation\Http\FormRequest;

class IssueTimeoutRequest extends FormRequest
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
            'reason_key' => ['bail', 'required', 'integer', new EnumValue(TimeoutReason::class, false)],
            'duration' => ['bail', 'required', 'integer', new EnumValue(TimeoutDuration::class, false)],
            'note' => ['bail', 'required', 'string', 'max:2000'],
        ];
    }
}
