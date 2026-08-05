<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\Role;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    use ApiResponse;

    /**
     * Display all roles and the number of users associated with each role.
     */
    public function index()
    {
        $roles = Role::withCount('users')
            ->latest()
            ->get();

        return $this->successResponse(
            RoleResource::collection($roles),
            'Roles retrieved successfully.'
        );
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
