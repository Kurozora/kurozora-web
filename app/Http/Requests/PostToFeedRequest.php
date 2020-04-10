<?php

namespace App\Http\Requests;

use App\FeedMessage;
use Illuminate\Foundation\Http\FormRequest;

class PostToFeedRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'body'          => ['required', 'bail', 'string', 'min:2', 'max:' . FeedMessage::MAX_BODY_LENGTH],
            'in_reply_to'   => ['numeric', 'exists:' . FeedMessage::TABLE_NAME . ',id']
        ];
    }
}
