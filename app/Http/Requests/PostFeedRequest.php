<?php

namespace App\Http\Requests;

use App\FeedMessage;
use Illuminate\Foundation\Http\FormRequest;

class PostFeedRequest extends FormRequest
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
            'parent_id'     => ['bail', 'integer', 'required_with:is_reply,is_reshare', 'exists:' . FeedMessage::TABLE_NAME . ',id'],
            'is_reply'      => ['bail', 'required_with:parent_id', 'different:is_reshare', 'integer', 'in:0,1', 'nullable'],
            'is_reshare'    => ['bail', 'required_with:parent_id', 'different:is_reply', 'integer', 'in:0,1', 'nullable'],
            'is_nsfw'       => ['bail', 'required', 'integer', 'in:0,1', 'nullable'],
            'is_spoiler'    => ['bail', 'required', 'integer', 'in:0,1', 'nullable']
        ];
    }
}
