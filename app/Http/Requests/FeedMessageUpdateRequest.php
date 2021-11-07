<?php

namespace App\Http\Requests;

use App\Models\FeedMessage;
use Illuminate\Foundation\Http\FormRequest;

class FeedMessageUpdateRequest extends FormRequest
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
            'body'          => ['bail', 'required', 'string', 'min:2', 'max:' . FeedMessage::MAX_BODY_LENGTH],
            'is_nsfw'       => ['bail', 'required', 'integer', 'in:0,1', 'nullable'],
            'is_spoiler'    => ['bail', 'required', 'integer', 'in:0,1', 'nullable']
        ];
    }
}
