<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    //

    /**
     * Display all roles.
     */
    public function index()
    {
        $roles = Role::withCount('users')
            ->latest()
            ->get();

        return response()->json($roles);
    }

    /**
     * Display a specific role with its users.
     */
    public function show($id)
    {
        $role = Role::with('users')->findOrFail($id);

        return response()->json($role);
    }
}
