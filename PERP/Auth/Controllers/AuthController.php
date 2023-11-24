<?php

namespace PERP\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PERP\Auth\FormRequests\UserSigninRequest;
use PERP\Auth\FormRequests\UserSignupRequest;
use PERP\Auth\Responses\Resource\AuthResource;
use PERP\Auth\Services\AuthService;
use PERP\Traits\ApiResponser;

class AuthController extends Controller
{
    use ApiResponser;

    public function login(UserSigninRequest $request)
    {
        try {
            $auth = (new AuthService)->login();

            $user = request()->user();

            $token = $auth->createUserToken($user);

        } catch (\Exception $e) {

            return $this->error($e->getMessage(), $e->getCode());
        }

        return $this->success(
            [
                'user' =>  (new AuthResource($user)),
                'access_token' =>  $token,
            ],
            'User successfully logged in'
        );
    }
    public function register(UserSignupRequest $request)
    {
        DB::beginTransaction();

        try {
            $auth = (new AuthService)->register();

            $user = $auth->user();

            $token = $user->createUserToken($user);
        } catch (\Exception $e) {

            DB::rollBack();

            return $this->error($e->getMessage(), $e->getCode());
        }

        DB::commit();

        return $this->success(
            [
                'user' =>  (new AuthResource($user)),
                'access_token' =>  $token,
            ],
            'User successfully created',
            201
        );
    }

    public function user(Request $request) {
        return $request->user();
    }

    public function logout(Request $request) {

        $request->user()->currentAccessToken()->delete();

        return true;
    }
}
