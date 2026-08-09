<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    //
    public function index()
    {
        $users = User::with('roles')
            ->latest()
            ->paginate(10);

        return response()->json($users);
    }

    // Get users by role
    public function showByRole($role)
    {
        $users = User::with('roles')
            ->whereHas('roles', function ($query) use ($role) {
                $query->where('name', $role);
            })
            ->get();

        return response()->json($users);
    }

    // Get user by ID
    public function show($id)
    {
        $user = User::with('roles')->findOrFail($id);

        return response()->json($user);
    }
}
