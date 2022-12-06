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
            // TODO: remove body and make content always required
            'body'          => ['bail', 'required_without:content', 'string', 'min:2', 'max:' . FeedMessage::MAX_CONTENT_LENGTH],
            'content'       => ['bail', 'required_without:body', 'string', 'min:2', 'max:' . FeedMessage::MAX_CONTENT_LENGTH],
            'is_nsfw'       => ['bail', 'required', 'integer', 'in:0,1', 'nullable'],
            'is_spoiler'    => ['bail', 'required', 'integer', 'in:0,1', 'nullable']
        ];
    }
}
