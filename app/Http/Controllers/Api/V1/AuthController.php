<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends ApiController
{
    public function __construct(
        protected AuthService $authService
    ) {
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['nullable', 'string', 'in:admin,doctor,patient,staff'],
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed.', 422, $validator->errors()->toArray());
        }

        $result = $this->authService->register($validator->validated());

        return $this->success([
            'user' => [
                'user_id' => $result['user']->user_id,
                'username' => $result['user']->username,
                'email' => $result['user']->email,
                'role' => $result['user']->role,
            ],
            'token' => $result['token'],
        ], 'User registered successfully.', 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed.', 422, $validator->errors()->toArray());
        }

        $result = $this->authService->login($validator->validated());

        if (! $result) {
            return $this->error('Invalid credentials.', 401);
        }

        return $this->success([
            'user' => [
                'user_id' => $result['user']->user_id,
                'username' => $result['user']->username,
                'email' => $result['user']->email,
                'role' => $result['user']->role,
            ],
            'token' => $result['token'],
        ], 'Login successful.');
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request->user());

        return $this->success(null, 'Logged out successfully.');
    }

    public function me(Request $request)
    {
        $user = $this->authService->me($request->user());

        return $this->success([
            'user_id' => $user->user_id,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role,
            'doctor' => $user->doctor,
            'patient' => $user->patient,
        ], 'Current user retrieved successfully.');
    }
}
