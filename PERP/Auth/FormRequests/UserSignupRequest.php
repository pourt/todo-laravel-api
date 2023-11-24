<?php

namespace PERP\Auth\FormRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserSignupRequest extends FormRequest
{
    private $userRules;
    private $tenantUserRules;

    private $principalRules;

    public function __construct()
    {
        $this->userRules = [

        ];
    }
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    public function prepare()
    {
        $this->merge([
            'email' => strtolower(request()->email),
            'name' => strtolower(request()->first_name),
        ]);
    }

    public function rules()
    {
        return [
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email'),
            ],
            'password' => [
                'required',
            ],
            'name' => [
                'required',
            ]
        ];
    }

    public function messages()
    {
        return [
            'email.unique' => 'User with the same email address already exists.',
            'email.required' => 'Email is required.',
            'password.required' => 'Password is required.',
            'name.required' => 'Name is required.'
        ];
    }
}
