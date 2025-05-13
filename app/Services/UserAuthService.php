<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserAuthService
{
    public function __construct()
    {
    }

    public function register(array $credentials)
    {
        $user = User::create([
            'email' => $credentials['email'],
            'password' => Hash::make($credentials['password']),
            'gender' => $credentials['gender'],
            'name' => $credentials['name'] ?? null,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token
        ];
    }

    public function getUserProfile($user)
    {
        return $user;
    }
}
