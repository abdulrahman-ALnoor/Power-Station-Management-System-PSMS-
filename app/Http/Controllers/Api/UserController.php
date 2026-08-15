<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        //$query = User::with('roles');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // if ($request->filled('role')) {
        //     $role = $request->role;
        //     $query->whereHas('roles', function ($q) use ($role) {
        //         $q->where('name', $role);
        //     });
        // }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->latest()->paginate(10);

        return $this->success('تم جلب الموظفين بنجاح.', $users);
    }

    public function stats()
    {
        $total = User::count();
        $active = User::where('status', 'active')->count();
        $inactive = User::where('status', 'inactive')->count();

        // $byRole = User::query()
        //     ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
        //     ->join('roles', 'roles.id', '=', 'user_roles.role_id')
        //     ->selectRaw('roles.name as role_name, count(distinct users.id) as total')
        //     ->groupBy('roles.name')
        //     ->pluck('total', 'role_name');

        // return $this->success('تم جلب إحصائيات الموظفين بنجاح.', [
        //     'total_employees'    => $total,
        //     'active_employees'   => $active,
        //     'inactive_employees' => $inactive,
        //     'by_role'            => $byRole,
        // ]);
    }

    // public function showByRole($role)
    // {
    //     $users = User::with('roles')
    //         ->whereHas('roles', function ($query) use ($role) {
    //             $query->where('name', $role);
    //         })
    //         ->get();

    //     return $this->success('تم جلب الموظفين حسب المسمى الوظيفي بنجاح.', $users);
    // }

    // public function show($id)
    // {
    //     $user = User::with('roles')->findOrFail($id);

    //     return $this->success('تم جلب بيانات الموظف بنجاح.', $user);
    // }
}
