<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

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
