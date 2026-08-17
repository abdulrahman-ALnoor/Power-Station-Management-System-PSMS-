<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;

class EquipmentController extends Controller
{
    use ApiResponse;

    // --------------------------------------------------------
    // الدوال الجديدة المطابقة للواجهة 5 (إدارة المعدات)
    // --------------------------------------------------------
    public function stats()
    {
        $stats = [
            'total_equipment'     => Equipment::count(),
            'assigned_equipment'  => Equipment::whereNotNull('user_id')->count(),
            'available_equipment' => Equipment::whereNull('user_id')->where('status', 'available')->count(),
            'maintenance_needed'  => Equipment::where('status', 'maintenance')->count(),
            'damaged_equipment'   => Equipment::where('status', 'damaged')->count(),
        ];

        return $this->success('تم جلب إحصائيات المعدات بنجاح', $stats, 200);
    }

    public function index(Request $request)
    {
        $query = Equipment::with(['user', 'creator']);

        if ($request->filled('status')) {
            if ($request->status === 'assigned') {
                $query->whereNotNull('user_id');
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            
            $query->where(function($q) use ($search) {
                $q->where('equipment_name', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function($u) use ($search) {
                      $u->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $equipments = $query->latest()->paginate(10);

        return $this->success('تم جلب قائمة المعدات بنجاح', $equipments, 200);
    }

    // --------------------------------------------------------
    // دوالك السابقة كما هي
    // --------------------------------------------------------
    public function show(Equipment $equipment)
    {
        $equipment->load([
            'user',
            'creator',
        ]);

        return $this->success('تم جلب بيانات المعدة بنجاح', $equipment, 200);
    }

   public function showByUser(int $userId)
    {
        $equipment = Equipment::with([
            'user',
            'creator',
        ])
            ->where('user_id', $userId)
            ->latest()
            ->get();

        return $this->success('تم جلب معدات الموظف بنجاح', $equipment, 200);
    }
}