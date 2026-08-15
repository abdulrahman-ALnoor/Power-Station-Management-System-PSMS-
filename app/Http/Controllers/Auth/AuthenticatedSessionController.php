<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AuthenticatedSessionController extends Controller
{
    use ApiResponse;
    /**
     * Handle an incoming authentication request.
     */
    // tokens authentication
    public function store(LoginRequest $request): JsonResponse
{
    $request->authenticate();

    $user = $request->user();

    $token = $user->createToken('auth-token')->plainTextToken;

        return $this->success(
            'Logged in successfully',
            [
                'user' => $user,
                'token' => $token,
            ]
        );
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();



        return $this->success('Logged out successfully');
    }
}
