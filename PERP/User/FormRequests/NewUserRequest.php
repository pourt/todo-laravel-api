<?php

namespace PERP\User\FormRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NewUserRequest extends FormRequest
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
            'first_name' => strtolower(request()->first_name),
            'last_name' => strtolower(request()->last_name),
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
