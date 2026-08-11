<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use Illuminate\Http\Request;
use App\Http\Requests\Equipment\StoreEquipmentRequest;
use App\Http\Requests\Equipment\UpdateEquipmentRequest;
use App\Http\Resources\Equipment\EquipmentResource;
use App\Traits\ApiResponse;

class EquipmentController extends Controller
{

    use ApiResponse;
        public function myStats(Request $request)
    {
        $userId = $request->user()->id;

        $totalEquipment = Equipment::where('user_id', $userId)->count();

        $byStatus = Equipment::where('user_id', $userId)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return $this->success('تم جلب إحصائيات المهندس بنجاح.', [
            'total_equipment' => $totalEquipment,
            'by_status' => $byStatus,
        ]);
    }
    public function stats()
    {
        $totalEquipment = Equipment::count();

        $byStatus = Equipment::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return $this->success(
            'تم جلب إحصائيات المعدات بنجاح.',
            [
                'total_equipment' => $totalEquipment,
                'by_status' => $byStatus,
            ]
        );
    }

    // Get all equipment
    // use the EquipmentResource to format the response
    // use the with() method to eager load the related models
        public function index(Request $request)
    {
        $query = Equipment::with(['user', 'creator'])
            ->where('user_id', $request->user()->id);

        // بحث باسم المعدة أو رقمها التسلسلي
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('equipment_name', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        // فلترة حسب الحالة
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $equipment = $query->latest()->paginate(10);

        return $this->success('تم جلب المعدات بنجاح.', $equipment);
    }

    // Store a newly created equipment in storage.
    // use the EquipmentResource to format the response
    // use the with() method to eager load the related models
    // use the StoreEquipmentRequest to validate the request data
    public function store(StoreEquipmentRequest $request)
    {
        $data = $request->validated();

        $equipment = Equipment::create($data);

        $equipment->load([
            'user',
            'creator',
        ]);

        return $this->success(
            'Equipment created successfully.',
            new EquipmentResource($equipment),
            201
        );
    }

    // Display the specified equipment.
    // use the EquipmentResource to format the response
    // use the with() method to eager load the related models
    public function show(Equipment $equipment)
    {
        $equipment->load([
            'user',
            'creator',
        ]);

        return $this->success(
            'Equipment retrieved successfully.',
            new EquipmentResource($equipment)
        );
    }

    // Update the specified equipment in storage.
    // use the EquipmentResource to format the response
    // use the with() method to eager load the related models
    // use the UpdateEquipmentRequest to validate the request data
    public function update(
        UpdateEquipmentRequest $request,
        Equipment $equipment
    ) {
        $data = $request->validated();

        $equipment->update($data);

        $equipment->load([
            'user',
            'creator',
        ]);

        return $this->success(
            'Equipment updated successfully.',
            new EquipmentResource($equipment)
        );
    }

    // Remove the specified equipment from storage.
    // use the EquipmentResource to format the response
    // use the with() method to eager load the related models
    public function destroy(Equipment $equipment)
    {
        $equipment->delete();

        return $this->success(
            'Equipment deleted successfully.'
        );
    }


    // function with out route

    public function showByUser($userId)
    {
        $equipment = Equipment::with([
            'user',
            'creator',
        ])
            ->where('user_id', $userId)
            ->latest()
            ->get();

        return $this->success(
            'Equipment retrieved successfully.',
            EquipmentResource::collection($equipment)
        );
    }
}
