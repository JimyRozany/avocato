<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    use ApiResponse;

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string',
            'email'    => 'required|email|unique:users,email',
            'mobile'   => 'required|string',
            'password' => 'required|string|min:8',
            'type'     => 'required|string|in:client,avocato',
            'bio'      => 'nullable|string',
        ]);

        try {
            $validated['password'] = Hash::make($validated['password']);
            $validated['is_active'] = $validated['type'] === 'client';
            $validated['rate'] = $validated['type'] === 'avocato' ? rand(50, 200) : null;

            $user = User::create($validated);
            $role = Role::findByName($validated['type'], 'api');
            $user->assignRole($role);

            $user->tokens()->delete();
            $token = $user->createToken('api-token')->plainTextToken;

            $response = [
                'user'  => $user,
                'role'  => $user->getRoleNames(),
                'token' => $token,
            ];

            if ($user->type === 'client') {
                $response['total_cases'] = 0;
            } else {
                $response['active_cases'] = 0;
            }

            return $this->successResponse($response, 'Registered successfully', 201);
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage(), 500);
        }
    }


    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = Auth::user();

        // delete old tokens (optional but recommended)
        $user->tokens()->delete();

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'role' => $user->getRoleNames(), // Spatie roles
            'token' => $token
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
            'roles' => $request->user()->getRoleNames()
        ]);
    }
}
