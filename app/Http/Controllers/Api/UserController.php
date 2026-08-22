<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    use ApiResponse;

    /**
     * عرض قائمة الموظفين (مع الأدوار)
     */
    public function index(Request $request)
    {
        $query = User::with('roles');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $role = $request->role;
            $query->whereHas('roles', function ($q) use ($role) {
                $q->where('name', $role);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->latest()->paginate($request->get('per_page', 10));

        return $this->success('تم جلب الموظفين بنجاح.', $users);
    }

    /**
     * إحصائيات الموظفين (الإجمالي، النشط، غير النشط، حسب الدور)
     */
    public function stats()
    {
        $total = User::count();
        $active = User::where('status', 'active')->count();
        $inactive = User::where('status', 'inactive')->count();

        $byRole = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_type', User::class)
            ->selectRaw('roles.name as role_name, count(distinct model_has_roles.model_id) as total')
            ->groupBy('roles.name')
            ->pluck('total', 'role_name');

        return $this->success('تم جلب إحصائيات الموظفين بنجاح.', [
            'total_employees'    => $total,
            'active_employees'   => $active,
            'inactive_employees' => $inactive,
            'by_role'            => $byRole,
        ]);
    }

    /**
     * عرض بيانات موظف واحد
     */
    public function show(int $id)
    {
        $user = User::with('roles')->findOrFail($id);

        return $this->success('تم جلب بيانات الموظف بنجاح.', $user);
    }

    /**
     * إضافة موظف جديد + تعيين دوره
     */
    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone'    => $validated['phone'] ?? null,
            'status'   => $validated['status'] ?? 'active',
        ]);

        $user->assignRole($validated['role']);

        return $this->success(
            'تم إضافة الموظف بنجاح.',
            $user->load('roles'),
            201
        );
    }

    /**
     * تحديث بيانات موظف (وتغيير دوره إذا أُرسل)
     */
    public function update(UpdateUserRequest $request, int $id)
    {
        $user = User::findOrFail($id);
        $validated = $request->validated();

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $role = $validated['role'] ?? null;
        unset($validated['role']);

        $user->update($validated);

        if ($role) {
            $user->syncRoles([$role]);
        }

        return $this->success('تم تحديث بيانات الموظف بنجاح.', $user->load('roles'));
    }

    /**
     * حذف موظف
     */
    public function destroy(int $id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return $this->success('تم حذف الموظف بنجاح.');
    }

    /**
     * عرض الموظفين حسب المسمى الوظيفي (الدور)
     */
    public function showByRole(string $role)
    {
        $users = User::with('roles')
            ->whereHas('roles', function ($query) use ($role) {
                $query->where('name', $role);
            })
            ->get();

        return $this->success('تم جلب الموظفين حسب المسمى الوظيفي بنجاح.', $users);
    }
}
