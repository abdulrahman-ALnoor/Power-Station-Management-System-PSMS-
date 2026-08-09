<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Http\Resources\RoleResource as ResourcesRoleResource;
use App\Models\Role;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    use ApiResponse;

    
     // Display all roles and the number of users associated with each role.
    public function index()
    {
        $roles = Role::withCount('users')
            ->latest()
            ->get();

        return $this->success(
            'Roles retrieved successfully.',
            RoleResource::collection($roles)
        );
    }


// Store a newly created role in storage.
    public function store(StoreRoleRequest $request)
    {
        $role = Role::create($request->validated());

        $role->loadCount('users');

        return $this->success(
            'Role created successfully.',
            new RoleResource($role),
            201 
        );
    }
    /**
     * Display a specific role with its users.
     */
    public function show(Role $role)
    {

        $role->loadCount([
            'users'
        ]);        return $this->success(
            'Role retrieved successfully.',
            new RoleResource($role)
        );
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        $role->update($request->validated());

        $role->loadCount('users');

        return $this->success(
            'Role updated successfully.',
            new RoleResource($role)
        );
    }

    // Delete a role from storage.
    // check if the role is assigned to any users before deleting
    public function destroy(Role $role)
    {
        // Check if the role is assigned to any users before deleting
        if ($role->users()->exists()) {
            return $this->error(
                'Cannot delete role because it is assigned to users.',
                409
            );
        }

        $role->delete();

        return $this->success(
            'Role deleted successfully.',
            null,
            200
        );
    }
}
