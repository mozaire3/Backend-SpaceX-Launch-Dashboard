<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Exceptions\UnauthorizedException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Register a new user
     */
    public function register(array $data): array
    {
        // Validate data
        $this->validateRegistrationData($data);

        // Create user
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'] ?? 'USER', // Default to USER role
        ]);

        // Generate JWT token
        $token = JWTAuth::fromUser($user);

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60, // Convert minutes to seconds
        ];
    }

    /**
     * Login user and return JWT token
     */
    public function login(string $email, string $password): array
    {
        // Validate credentials
        $credentials = compact('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                throw new UnauthorizedException('Invalid credentials');
            }
        } catch (JWTException $e) {
            throw new UnauthorizedException('Could not create token');
        }

        $user = Auth::user();

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
        ];
    }

    /**
     * Logout user (invalidate token)
     */
    public function logout(): void
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
        } catch (JWTException $e) {
            throw new UnauthorizedException('Could not logout user');
        }
    }

    /**
     * Refresh JWT token
     */
    public function refresh(): array
    {
        try {
            $token = JWTAuth::refresh(JWTAuth::getToken());
            $user = JWTAuth::setToken($token)->toUser();

            return [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl') * 60,
            ];
        } catch (JWTException $e) {
            throw new UnauthorizedException('Could not refresh token');
        }
    }

    /**
     * Get authenticated user profile
     */
    public function getProfile(): array
    {
        $user = Auth::user();

        if (!$user) {
            throw new UnauthorizedException('User not authenticated');
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'is_admin' => $user->isAdmin(),
            'created_at' => $user->created_at->toISOString(),
        ];
    }

    /**
     * Update user profile
     */
    public function updateProfile(array $data): array
    {
        $user = Auth::user();

        if (!$user) {
            throw new UnauthorizedException('User not authenticated');
        }

        // Update allowed fields only
        $updateData = [];
        
        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
        }

        if (isset($data['email']) && $data['email'] !== $user->email) {
            // Check if email is unique
            if (User::where('email', $data['email'])->where('id', '!=', $user->id)->exists()) {
                throw ValidationException::withMessages([
                    'email' => ['Email already taken'],
                ]);
            }
            $updateData['email'] = $data['email'];
        }

        if (isset($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        if (!empty($updateData)) {
            $user->update($updateData);
            $user->refresh();
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ];
    }

    /**
     * Check if user has admin role
     */
    public function isAdmin(): bool
    {
        $user = Auth::user();
        return $user && $user->isAdmin();
    }

    /**
     * Validate registration data
     */
    private function validateRegistrationData(array $data): void
    {
        // Check if email already exists
        if (User::where('email', $data['email'])->exists()) {
            throw ValidationException::withMessages([
                'email' => ['Email already taken'],
            ]);
        }

        // Validate role if provided
        if (isset($data['role']) && !in_array($data['role'], ['USER', 'ADMIN'])) {
            throw ValidationException::withMessages([
                'role' => ['Invalid role'],
            ]);
        }
    }
}