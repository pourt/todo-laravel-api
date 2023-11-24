<?php

namespace PERP\Auth\FormRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserSigninRequest extends FormRequest
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


    public function rules()
    {
        return [
            'email' => [
                'required',
                'email',
            ],
            'password' => [
                'required',
            ],
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'Email is required.',
            'email.email' => 'Email must be a valid emaila ddress.',
            'password.required' => 'Password is required.',
        ];
    }
}
