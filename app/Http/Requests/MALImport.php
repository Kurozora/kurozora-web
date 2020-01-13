<?php

namespace App\Http\Requests;

use App\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MALImport extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = User::find($this->route('user'))->first();

        return $user && $this->user()->can('mal_import', $user);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'file'      => ['required', 'file', 'mimes:xml', 'max:' . config('mal-import.max_xml_file_size')],
            'behavior'  => ['required', 'string', Rule::in(['overwrite'])]
        ];
    }
}
