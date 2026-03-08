<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Login and issue API token.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'The provided credentials are incorrect.'], 422);
        }

        if (!$user->is_active) {
            return response()->json(['message' => 'Account is inactive.'], 403);
        }

        // Revoke previous tokens (optional: one device per user) or allow multiple
        // $user->tokens()->delete();

        $token = $user->createToken('farmhandpro-login')->plainTextToken;

        $user->load('farm');

        return response()->json([
            'token' => $token,
            'user' => $user->only(['id', 'name', 'email', 'role', 'farm_id', 'farm']),
        ]);
    }

    /**
     * Logout and revoke current token.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    /**
     * Register a new user (e.g. worker sign-up).
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:50',
            'role' => 'nullable|in:worker,supervisor,owner,admin',
            'farm_id' => 'nullable|exists:farms,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'role' => $request->role ?? 'worker',
            'farm_id' => $request->farm_id,
            'is_active' => true,
        ]);

        $token = $user->createToken('farmhandpro-login')->plainTextToken;
        $user->load('farm');

        return response()->json([
            'token' => $token,
            'user' => $user->only(['id', 'name', 'email', 'role', 'farm_id', 'farm']),
        ], 201);
    }

    /**
     * Return the authenticated user.
     */
    public function me(Request $request)
    {
        $user = $request->user();
        $user->load('farm');

        return response()->json($user->only(['id', 'name', 'email', 'role', 'phone', 'farm_id', 'farm', 'is_active']));
    }
}
