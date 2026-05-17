<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function login(array $credentials): User
    {
        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        Auth::login($user);

        // Optional: Prevent inactive users (if you add status later)
        // if ($user->status !== 'Active') { ... }

        // Revoke old tokens (optional: keep multiple or revoke all)
        $user->tokens()->delete();

        // Create new token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Attach token to user object for resource
        $user->token = $token;

        return $user;
    }

    public function logout(User $user): void
    {
        Auth::logout();
        // $user->currentAccessToken()->delete();
    }

    public function me(User $user): User
    {
        return $user->loadMissing(['stockIns']); // optional: eager load if needed
    }
}
