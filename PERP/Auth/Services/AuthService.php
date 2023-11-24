<?php

namespace PERP\Auth\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    public $user = null;

    public function login()
    {
        $credentials = request(['email','password']);
        if(!Auth::attempt($credentials))
        {
            throw new \Exception('Sorry, we are unable to retrieve user', 401);
        }

        return $this;
    }

    public function register()
    {
        $this->user = User::create(request()->toArray());

        if (!$this->user) {
            throw new \Exception('Sorry, we are unable to create user information', 400);
        }

        return $this;
    }

    public function user()
    {
        return $this->user;
    }

    public function createUserToken($user)
    {
        $tokenResult = $user->createToken('Personal Access Token')->plainTextToken;

        return [
            'value' => $tokenResult,
            'type' => 'Bearer',
        ];
    }

}
