<?php

namespace PERP\User\Services;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use PERP\Auth\Models\Role;
use PERP\User\Models\User;
use PERP\TwoFactorAuth\Services\TwoFactorService;

class UserService
{
    public function getUserLists(Closure $whereClosure, $trashed = false)
    {
        $user = User::with(['roles'])
            ->where($whereClosure);

        if ($trashed) {
            $user = $user->withTrashed();
        }

        $user = $user->paginate(request('perPage', $user->count()));

        if (!$user) {
            throw new \Exception('Sorry, we are unable to retrieve user lists', 404);
        }

        return $user;
    }

    public function getUser(Closure $closure, $withTrashed = false): User
    {
        $user = User::with(['roles'])->where($closure);
        if ($withTrashed) {
            $user = $user->withTrashed();
        }
        $user = $user->first();

        if (!$user) {
            throw new \Exception('Account not found. Please check the email.', 404);
        }

        return $user;
    }

    public function findUser()
    {
        if (!sizeof(request()->all())) {
            throw new \Exception("Please add a parameter for the condition", 400);
        }

        $validator = Validator::make(['email' => request()->email], [
            'email' => 'email'
        ]);

        if ($validator->fails()) {
            throw new \Exception('Invalid user Id.', 400);
        }

        $user = User::where(function ($query) {
            $query->where('email', request()->email);
        })->first();

        if (!$user) {
            throw new \Exception('Sorry, we are unable to retrieve user', 404);
        }

        return $user;
    }

    public function getUserByEmail()
    {
        $inputs = [
            'roles' => request()->roles,
        ];

        $rules = [
            'roles' => [
                'required',
                Rule::exists('roles', 'system_name')->where(function ($query) {
                    return $query->whereIn('system_name', request()->roles);
                }),
            ]
        ];

        $messages = [
            'required' => ':attribute is required.',
            'exists' => ':attribute does not exist',
        ];

        $validator = Validator::make($inputs, $rules, $messages);

        if ($validator->fails()) {
            throw new \Exception($validator->errors(), 400);
        }

        $user = User::with(['roles'])->where(function ($query) {

            if (request()->roles) {
                $query->whereHas('roles', function ($query) {
                    $query->whereIn('role_id', request()->roles);
                });
            }

            $query->where('email', request()->email);
        })->first();

        return $user;
    }

    public function getUserById($id): User
    {
        $user = User::with(['roles'])->where(function ($query) use ($id) {
            $query->where("id", $id);
        })->first();

        return $user;
    }

    public function newUserAccount(): User
    {


        $user = User::create(request()->toArray());

        if (!$user) {
            throw new \Exception('Sorry, we are unable to create user information', 400);
        }

        return $user;
    }

    public function newTenantUserAccount(): User
    {
        if (isset(request()->roles) &&  request()->roles) {
            $inputs = [
                'roles' => request()->roles,
            ];

            $rules = [
                'roles' => [
                    'required',
                    Rule::exists('roles', 'system_name')->where(function ($query) {
                        return $query->whereIn('system_name', request()->roles);
                    }),
                ]
            ];

            $messages = [
                'required' => ':attribute is required.',
                'exists' => ':attribute does not exist',
            ];

            $validator = Validator::make($inputs, $rules, $messages);

            if ($validator->fails()) {
                throw new \Exception($validator->errors(), 400);
            }
        }

        $user = User::updateOrCreate(['email' => request()->email], request()->toArray());


        if (!$user) {
            throw new \Exception('Sorry, we are unable to create user information', 400);
        }

        if (isset(request()->roles) &&  request()->roles) {
            $roleIds = Role::getRoleIds(request()->roles);
            $user->roles()->sync($roleIds);
        }

        return $user;
    }

    public function updateUserAccount(User $user)
    {
        $user->fill(request()->toArray());

        if (!$user->save()) {
            throw new \Exception('Sorry, we are unable to modify user information', 400);
        }

        return $user;
    }

    public function restoreUserAccount(User $user)
    {
        $user->restore();

        if (!$user) {
            throw new \Exception('Sorry, we are unable to restore user information', 400);
        }

        return $user;
    }

    public function deleteUserAccount(User $user)
    {
        if (!$user->delete()) {
            throw new \Exception('Sorry, we are unable to delete user', 404);
        }

        return $user;
    }

    public function newUser(Request $request, $role = 'va'): User
    {
        $user = User::create($request->toArray());

        if (!$user) {
            throw new \Exception('Sorry, we are unable to create client user information', 400);
        }

        $roleId = Role::getRoleId($role);
        $user->roles()->sync([$roleId]);

        return $user;
    }

    public function enableTwoFactor(Request $request, $userId)
    {
        if ($userId) {
            throw new \Exception('Invalid user account identifier.', 400);
        }

        $user = User::find($userId);

        if (!$user) {
            throw new \Exception('Sorry, we are unable to retrieve your account.', 404);
        }

        $QRCode = (new TwoFactorService($user))->generateTwoFactorQRCode();

        if (!$QRCode) {
            throw new \Exception('Sorry, we are unable to generate two-factor QR Code. Please try again.', 406);
        }


        /**
         * @todo Delete bearer token to re-authenticate with two-factor code
         **/

        return $QRCode;
    }
}
