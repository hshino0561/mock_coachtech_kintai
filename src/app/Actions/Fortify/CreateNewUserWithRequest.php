<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\RegisterRequest;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreateNewUserWithRequest implements CreatesNewUsers
{
    public function create(array $input): User
    {
        // RegisterRequest の rules と messages を使ってバリデーション
        $request = new RegisterRequest();
        $validator = Validator::make($input, $request->rules(), $request->messages());

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validated = $validator->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_first_login' => false,
        ]);

        // 認証メール発行
        // event(new Registered($user));
        // Auth::login($user);

        return $user;
    }
}
