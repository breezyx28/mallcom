<?php

namespace App\Http\Requests;

use App\Rules\dateFormatRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UsersRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'firstName' => 'required|string|max:191',
            'username' => 'required|unique:users|string|max:191',
            'middleName' => 'string|max:191',
            'lastName' => 'required|string|max:191',
            'thumbnail' => 'image|mimes:jpg,jpeg,png',
            'phone' => 'required|unique:users,phone|digits:10',
            'email' => 'string|regex:/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/|max:191',
            'address' => 'string|max:191',
            'state_id' => 'required|exists:states,id|integer',
            // 'birthDate' => ['date', new dateFormatRule()],
            'birthDate' => 'date',
            'gender' => ['required', Rule::in(['ذكر', 'انثى'])],
            'password' => 'required|string|max:191',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();
        $messages = [];
        foreach ($errors->all() as $message) {
            $messages[] = $message;
        }
        throw new HttpResponseException(response()->json(['success' => false, 'errors' => $messages], 200));
    }
}
