<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\NewAccessToken;

class AuthService
{
    public function registerUser(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password'])
        ]);
    }

    public function createLoginToken(User $user): NewAccessToken
    {
        return $user->createToken('login_token');
    }

    public function logout(User $user): void
    {
        if ($user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }
    }
}