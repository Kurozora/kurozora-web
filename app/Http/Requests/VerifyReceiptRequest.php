<?php

namespace App\Http\Requests;

use App\Rules\ValidateBase64;
use App\Rules\ValidateHexadecimal;
use Illuminate\Foundation\Http\FormRequest;

class VerifyReceiptRequest extends FormRequest
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
            'receipt' => ['bail', 'required_without:transactions', new ValidateBase64()],
            'password' => ['bail', 'nullable', new ValidateHexadecimal()],
            'transactions' => ['bail', 'required_without:receipt', 'array', 'min:1', 'max:50'],
            'transactions.*' => ['required', 'string'],
            'environment' => ['nullable', 'string', 'in:Production,Sandbox,Xcode,production,sandbox,xcode'],
        ];
    }
}
