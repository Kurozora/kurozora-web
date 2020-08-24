<?php

namespace App\Http\Requests;

use App\FeedMessage;
use Illuminate\Foundation\Http\FormRequest;

class PostFeedRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'body'          => ['bail', 'required', 'string', 'min:2', 'max:' . FeedMessage::MAX_BODY_LENGTH],
            'parent_id'     => ['bail', 'integer', 'exists:' . FeedMessage::TABLE_NAME . ',id'],
            'is_nsfw'       => ['bail', 'required', 'integer', 'in:0,1'],
            'is_spoiler'    => ['bail', 'required', 'integer', 'in:0,1']
        ];
    }
}
